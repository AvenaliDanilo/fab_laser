#!/bin/env python
# -*- coding: utf-8; -*-
#
# (c) 2016 FABtotum, http://www.fabtotum.com
#
# This file is part of FABUI.
#
# FABUI is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# FABUI is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with FABUI.  If not, see <http://www.gnu.org/licenses/>.

# Import standard python module
import argparse
import time
import gettext
import json
import os

# Import external modules
import numpy as np
import cv2,cv
from loaders import dxfgrabber

# Import internal modules
from common.drawing import Drawing2D
from output.laser import LaserEngraver
from output.debug import DebugEngraver

# Set up message catalog access
tr = gettext.translation('img2gcode', 'locale', fallback=True)
_ = tr.ugettext

def preprocess_dxf_image(filename): 
    output = Drawing2D()
    output.load_from_dxf(filename)
    return output

def preprocess_raster_image(image_file, target_width, target_height, dot_size, levels = 6, invert = False, crop = '', debug=False):
    """
    Convert a raster image to horizontal line list classified into levels by color intensity.
    Only width or height has to be non-zero as the other value is automatically calculated
    based on the image width/height ration.
    
    :param image_file: Raster image filename.
    :param target_width: Target width in mm.
    :param target_height: Target height in mm.
    :param dot_size: Smallest engraver detail (mm).
    :param levels: Number of gray levels.
    :param invert: Invert gray intensity.
    :param crop: Crop image (x,y,w,h) (pixels)
    """
    
    img = cv2.imread(image_file)   
    
    #### Resize: BEGIN ####
    
    h, w = img.shape[:2]
    
    # Minimal dots per mm
    min_dpm = 1 #int(1 / dot_size)
    
    dpm = int(1 / dot_size)
    
    # Default scaling factor for Width
    sx = 1.0
    # Default scaling factor for Height
    sy = 1.0
    
    new_w = w
    new_h = h
    
    if target_width:
        # Dots per mm based on given parameters
        dpm_x = target_width / float(w)
        
        # If DPM is less then minimal, scale the image so that pixels
        # are of proper size
        if dpm_x < 1.0:
            max_w = float(target_width)
            new_w = int( w * dpm_x * dpm )
            sx = 1.0 / (dpm_x * dpm)
            print "new_w", new_w
            
    if target_height:
        # Dots per mm based on given parameters
        dpm_y = float(target_height) / float(h)
        ppm_y = float(h) / float(target_height)

        # If DPM is less then minimal, scale the image so that pixels
        # are of proper size        
        if dpm_y < 1.0:
            max_h = float(target_height)
            new_h = int( h * dpm_y * dpm )
            sy = 1.0 / (dpm_y * dpm)
            print "new_h", new_h
    
    sx = w / float(new_w)
    sy = h / float(new_h)
    scale = max(sx,sy)
    print "scale", scale
        
    w = int(w / scale)
    h = int(h / scale)
    
    if target_width == 0:
        target_width =  (w * float(target_height)) / h
        
    if target_height == 0:
        target_height =  (h * float(target_width)) / w 
    
    if scale > 1:
        work_width = w
        work_height = h
    else:
        work_width = int(target_width / dot_size)
        work_height = int(target_height / dot_size)
    
    print "Target (mm)", target_width, target_height
    
    # Resize image if needed
    if scale != 1:
        print "Resize to {0}x{1}".format(w,h)
        img = cv2.resize(img,(w, h), interpolation = cv2.INTER_CUBIC)
    
    #### Resize: END ####
    
    if crop:
        crop = crop.split(',')
        x1 = int(crop[0])
        x2 = x1 + int(crop[2])
        y1 = int(crop[1])
        y2 = y1 + int(crop[3])
        print "Crop {0} {1} {2} {3}".format(x1, x2, y1, y2)
        img = img[y1:y2, x1:x2]
        #~ if debug:
            #~ cv2.imwrite('cropped.png', img) 
    
    # Flip image on the Y axis to compensate for image Y axis and 
    # machine Y axis orientation
    img = cv2.flip(img,0)
    
    # ?
    Z = img.reshape((-1,3))
    # Convert to np.float32
    Z = np.float32(Z)
    # Define criteria, number of clusters(K) and apply kmeans()
    criteria = (cv2.TERM_CRITERIA_EPS + cv2.TERM_CRITERIA_MAX_ITER, 10, 1.0)
    # Define number of levels
    K = levels
    ret,label,center = cv2.kmeans(Z, K, criteria, 10, cv2.KMEANS_RANDOM_CENTERS)
    
    shades_of_gray = np.ndarray( (center.shape[0], 1) , dtype=int)
    i = 0
    for c in center:
        x = c[0] * 0.3 + c[1] * 0.59 + c[2] * 0.11
        if invert:
            x = 255 - int(x)
        shades_of_gray[i] = int(x)
        i += 1
    
    # Center is an array of cluster representative colors
    # label is an array of cluster labels for every pixel (label=[0..K-1] )
    h, w = img.shape[:2]
    
    flat_labels = label.flatten()
    
    res = shades_of_gray[flat_labels]
    res2 = res.reshape( (h,w,1) )
    img = res2

    # Invert image if requested
    if invert:
        img = 255 - img

    # save a preview for internal use
    #~ if debug:
        #~ cv2.imwrite('img_preprocess.png', img)
    
    sorted_geays = shades_of_gray.copy()
    sorted_geays.sort(axis=0)    
    mapped = range(shades_of_gray.shape[0])
    rmapped = range(shades_of_gray.shape[0])
    
    for i in xrange(sorted_geays.shape[0]):
        value = sorted_geays[i]
        idx = np.nonzero(shades_of_gray == value)
        j = int(idx[0])
        mapped[i] = j
        rmapped[j] = i
    
    results = {
        'level' : range(levels),
        'width' : w,
        'height' : h,
        'target_width' : target_width,
        'target_height' : target_height
    }
    
    for lvl in xrange(levels):
        
        value = int(shades_of_gray[ rmapped[lvl] ])
        
        results['level'][lvl] = {
            'lines' : [],
            'value' : value,
            'percentage' : float(value) / 255.0,
        }
        
        mask = np.zeros((shades_of_gray.shape), dtype=np.int)
        mask[ lvl ] = 255
        
        res = mask[flat_labels]
        res2 = res.reshape( (h,w,1) )

        # Invert image if requested
        if invert:
            res2 = 255 - res2

        # save a preview for internal use
        #~ if debug:
            #~ cv2.imwrite('img_preprocess_{0}.png'.format(lvl), res2) 
        
    for ii in xrange(len(mapped)):
        results['level'][ii]['lines'] = []
    
    results['work_width'] = work_width
    results['work_height'] = work_height
    
    for y in xrange(h):
        for ii in xrange(len(mapped)):
            results['level'][ii]['lines'].append([])
        
        old_lbl = -1
        new_lbl = -1
        start_x = 0
        
        for x in xrange(w):
            idx = y * w + x
            new_lbl = flat_labels[idx]
            if new_lbl != old_lbl:
                if x > start_x:
                    lbl = mapped[old_lbl]
                    
                    x1 = int( float(start_x * work_width) / w )
                    x2 = int( float(x * work_width) / w )
                    
                    results['level'][lbl]['lines'][y].append( (x1, x2) )
                old_lbl = new_lbl
                start_x = x
                
        if  x >= start_x:
            lbl = mapped[old_lbl]
            x1 = int( float(start_x * work_width) / w )
            x2 = int( float(x * work_width) / w )+1
            results['level'][lbl]['lines'][y].append( (x1, x2) )
    
    return results

def preprocess_raster_image_bw(image_file, target_width, target_height, dot_size, threshold = 127, invert = False, crop = '', debug=False):
    """
    Convert a raster image to horizontal line list classified into levels by color intensity.
    Only width or height has to be non-zero as the other value is automatically calculated
    based on the image width/height ration.
    
    :param image_file: Raster image filename.
    :param target_width: Target width in mm.
    :param target_height: Target height in mm.
    :param dot_size: Smallest engraver detail (mm).
    :param levels: Number of gray levels.
    :param invert: Invert gray intensity.
    :param crop: Crop image (x,y,w,h) (pixels)
    """
    
    img = cv2.imread(image_file)   
    
    #### Resize: BEGIN ####
    
    h, w = img.shape[:2]
    
    # Minimal dots per mm
    min_dpm = 1 #int(1 / dot_size)
    
    dpm = int(1 / dot_size)
    
    # Default scaling factor for Width
    sx = 1.0
    # Default scaling factor for Height
    sy = 1.0
    
    new_w = w
    new_h = h
    
    if target_width:
        # Dots per mm based on given parameters
        dpm_x = target_width / float(w)
        
        # If DPM is less then minimal, scale the image so that pixels
        # are of proper size
        if dpm_x < 1.0:
            max_w = float(target_width)
            new_w = int( w * dpm_x * dpm )
            sx = 1.0 / (dpm_x * dpm)
            print "new_w", new_w
            
    if target_height:
        # Dots per mm based on given parameters
        dpm_y = float(target_height) / float(h)
        ppm_y = float(h) / float(target_height)

        # If DPM is less then minimal, scale the image so that pixels
        # are of proper size        
        if dpm_y < 1.0:
            max_h = float(target_height)
            new_h = int( h * dpm_y * dpm )
            sy = 1.0 / (dpm_y * dpm)
            print "new_h", new_h
    
    sx = w / float(new_w)
    sy = h / float(new_h)
    scale = max(sx,sy)
    print "scale", scale
        
    w = int(w / scale)
    h = int(h / scale)
    
    if target_width == 0:
        target_width =  (w * float(target_height)) / h
        
    if target_height == 0:
        target_height =  (h * float(target_width)) / w 
    
    if scale > 1:
        work_width = w
        work_height = h
    else:
        work_width = int(target_width / dot_size)
        work_height = int(target_height / dot_size)
    
    print "Target (mm)", target_width, target_height
    
    # Resize image if needed
    if scale != 1:
        print "Resize to {0}x{1}".format(w,h)
        img = cv2.resize(img,(w, h), interpolation = cv2.INTER_CUBIC)
    
    #### Resize: END ####
    
    if crop:
        crop = crop.split(',')
        x1 = int(crop[0])
        x2 = x1 + int(crop[2])
        y1 = int(crop[1])
        y2 = y1 + int(crop[3])
        print "Crop {0} {1} {2} {3}".format(x1, x2, y1, y2)
        img = img[y1:y2, x1:x2]
        if debug:
            cv2.imwrite('cropped.png', img) 
    
    # Flip image on the Y axis to compensate for image Y axis and 
    # machine Y axis orientation
    img = cv2.flip(img,0)
    
    t = cv2.THRESH_BINARY
    if invert:
        t = cv2.THRESH_BINARY_INV
    
    ACTIVE_LABEL = 1
    
    gray_image = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    ret,thresh1 = cv2.threshold(gray_image, threshold, ACTIVE_LABEL, t)
    
    flat_labels = thresh1.flatten()
        
    # Center is an array of cluster representative colors
    # label is an array of cluster labels for every pixel (label=[0..K-1] )
    h, w = img.shape[:2]

    # save a preview for internal use
    if debug:
        # Invert image if requested
        img = thresh1 * 255
        cv2.imwrite('img_preprocess.png', img)
       
    results = {
        'level' : {
            0 : { 
                'lines' : [],
                'value' : 255,
                'percentage' : 1.0,
            }
        },
        'width' : w,
        'height' : h,
        'target_width' : target_width,
        'target_height' : target_height
    }
   
    results['work_width'] = work_width
    results['work_height'] = work_height
    
    for y in xrange(h):
        results['level'][0]['lines'].append([])
        
        old_lbl = -1
        new_lbl = -1
        start_x = 0
        
        for x in xrange(w):
            idx = y * w + x
            new_lbl = flat_labels[idx]
            if new_lbl != old_lbl:
                if x > start_x:
                    lbl = old_lbl
                    
                    x1 = int( float(start_x * work_width) / w )
                    x2 = int( float(x * work_width) / w )
                    if lbl == ACTIVE_LABEL:
                        results['level'][0]['lines'][y].append( (x1, x2) )
                
                start_x = x
            old_lbl = new_lbl
                
        if  x >= start_x:
            lbl = old_lbl
            x1 = int( float(start_x * work_width) / w )
            x2 = int( float(x * work_width) / w )+1
            if lbl == ACTIVE_LABEL:
                results['level'][0]['lines'][y].append( (x1, x2) )
    
    return results

def vectorize_raster(data, preset):
    """
    Draw horizontal lines.
    """
    work_width = data['work_width']
    width = data['width']
    work_height = data['work_height']
    height = data['height']
    
    output = Drawing2D()
    
    reverse = False
    interleave = False
    
    lvl = 0
    for level in data['level']:
        value = data['level'][lvl]['value']
        #~ percent = data['level'][lvl]['percentage']
    
        lyr_idx = output.add_layer('level_{0}'.format(lvl), value)
    
        #~ self.comment(" Level {0}".format(lvl))
        #self.level(value)
        
        old_img_y = -1
        y1 = 0
        
        #~ if 'interleave' in preset['skip']:
            #~ if preset['skip']['interleave']:
                #~ interleave = not interleave
        
        for y in xrange(work_height):
            # Get the pixel row corresponding to the burning y
            # Note: this is needed as a pixel might contain multiple laser lines (fat pixels)
            img_y = int((float(height) * y * 100.0) / float(work_height)) / 100
            
            # Detect the boundry of the pixel rows and create a fill_rect
            # command from it. This way it's easier to handle it as a bulk command.
            if old_img_y != img_y:
                
                #~ self.comment(' Row {0}'.format(y))
                
                old_img_y = img_y
                # Update row end
                y2 = y+1
                did_something = False
            
                # Get all the lines in this row
                lines = data['level'][lvl]['lines'][img_y]
                
                # Change the direction of burning to reduce laser movement
                if reverse:
                    lines = reversed(lines)
            
                for line in lines:
                    if reverse:
                        # Swap x1/x2 if reversed
                        x1 = line[1]
                        x2 = line[0]
                    else:
                        x1 = line[0]
                        x2 = line[1]
                    
                    output.add_rect(x1, y1, x2, y2, lyr_idx, filled=True)
                    did_something = True
                
                # If any burning happened, reverse the burning direction
                if did_something:
                    reverse = not reverse
                
                # Next row starts where this one ended
                y1 = y2
            
        lvl += 1
        
    return output
    
def main():
    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("preset_file",       help=_("Preset file [json file]"))
    parser.add_argument("image_file",       help=_("Image file [raster: jpg, png; vector: dxf]"))
    parser.add_argument("-o", "--output",   help=_("Output gcode file."),    default='laser.gcode')
    # Image preprocessing
    parser.add_argument("-l", "--levels",   help=_("Laser power levels [used for raster, range: 2..10]"),    default=6)
    parser.add_argument("-i", "--invert",   action='store_true', help=_("Invert color values"),   default=False)
    parser.add_argument("-d", "--debug",   action='store_true', help=_("Enable debug output"),   default=False)
    parser.add_argument("-C", "--crop",   help=_("Crop image. Use x,y,w,h'. Example: -c 0,0,100,100"),   default='')
    # Vectorization
    parser.add_argument("-W", "--width",    help=_("Engraving width"),        default=0)
    parser.add_argument("-H", "--height",    help=_("Engraving height"),      default=0)
    parser.add_argument("-D", "--dot-size",    help=_("Engraving dot size [mm]"),  default=0.1)
    parser.add_argument("-S", "--shortest-line",    help=_("Ignore lines shorter then this value [mm]"),  default=0.0)
    # Toolpath
    parser.add_argument("-O", "--optimize",    help=_("Optimize toolpath to reduce travel distance [0=off, 1=closest, 2=closes+reverse]"),  default=1)
    parser.add_argument("-s", "--statistics",  action='store_true', help=_("Output toolpath statistics"),  default=False)
    
    
    # GET ARGUMENTS
    args = parser.parse_args()

    # INIT VARs
    gcode_file      = args.output
    image_file      = args.image_file
    preset_file      = args.preset_file
    target_width    = float(args.width)
    target_height   = float(args.height)
    levels          = int(args.levels)
    invert          = bool(args.invert)
    debug          = bool(args.debug)
    crop            = args.crop
    dot_size        = float(args.dot_size)
    shortest_line   = float(args.shortest_line)
    optimize        = int(args.optimize)
    statistics      = bool(args.statistics)
    
    if levels < 1 or levels > 10:
        print "Level is out of allowed range"
        parser.print_help()
        exit(1)
    
    filename, ext = os.path.splitext(image_file)
    ext = ext.lower()
    
    with open(preset_file) as f:
        preset = json.load(f)
    
    if ext == '.dxf':
        drawing = preprocess_dxf_image(image_file)
        drawing.normalize()
        drawing.scale_to(target_width, target_height)
        
        lsr = LaserEngraver(gcode_file, (1.0, 1.0), preset, statistics=statistics)
        lsr.start()
        lsr.draw(drawing, optimize=optimize)
        lsr.end()
        
        if statistics:
            print lsr.stats
        
        if debug:
            #~ os.system('LC_NUMERIC=C camotics output.gcode')
            work_path = os.path.dirname(gcode_file)
            dbg_file = os.path.join(work_path,'debug.png')
            
            dpm = 1.0 / dot_size
            
            work_width = int(drawing.width() * dpm) + 5
            work_height = int(drawing.height() * dpm) + 5
            
            print "Debug (WxH):", work_width, work_height
            
            color = 0
            #~ if invert:
                #~ color = 255
            
            drawing.transform(dpm, dpm, 0, 0)
            
            dbg = DebugEngraver(dbg_file, work_width, work_height, color, dot_size, True, preset)
            dbg.start()
            dbg.draw(drawing)
            dbg.end()
        
    elif ext == '.jpg' or ext == '.jpeg' or ext == '.png':
        
        if levels > 1:
            result = preprocess_raster_image(image_file, target_width, target_height, dot_size, levels, invert, crop, debug=debug)
        else:
            result = preprocess_raster_image_bw(image_file, target_width, target_height, dot_size, 127, invert, crop, debug=debug)
            
        drawing = vectorize_raster(result, preset)

        s = dot_size
        
        lsr = LaserEngraver(gcode_file, (s,s), preset, statistics=statistics)
        lsr.start()
        lsr.draw(drawing, optimize=0)
        lsr.end()
        
        if statistics:
            print lsr.stats
        
        if debug:
            #~ os.system('LC_NUMERIC=C camotics output.gcode')
            
            work_path = os.path.dirname(gcode_file)
            dbg_file = os.path.join(work_path,'debug.png')
            
            color = 0
            #~ if invert:
                #~ color = 0
            
            dbg = DebugEngraver(dbg_file, result['work_width'], result['work_height'], color, dot_size, True, preset)
            dbg.start()
            dbg.draw(drawing)
            dbg.end()
    else:
        print "Unsupported filetype"
        exit(1)
    
if __name__ == "__main__":
    main()
