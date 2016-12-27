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

__authors__ = "Daniel Kesler"
__license__ = "GPL - https://opensource.org/licenses/GPL-3.0"
__version__ = "1.0"

# Import external modules
import numpy as np

# Import internal modules

class EngraverOutput(object):
    
    def __init__(self, preset):
        self.preset = preset
        self.interleave = False
        
        self.skip_function = {
            'modulo' : self.get_y_list_modulo,
        }
    
    def __sort_elements(self, elements, sort_list = [], reverse_list = [], use_reverse = False):
        
        if len(elements) == 0:
            return [], []
        
        if len(sort_list) == 0:
            cur_x = 0.0
            cur_y = 0.0
        else:
            last = sort_list[-1]
            if last in reverse_list:
                p0 = elements[last]['points'][0]
            else:
                p0 = elements[last]['points'][-1]
            cur_x = p0[0]
            cur_y = p0[1]
        
        closest = None
        reverse = None
        dist = 1e20
        
        for i in xrange( len(elements) ):
            #~ print elements[i]
            if i not in sort_list:
                e = elements[i]
                p0 = e['points'][0]
                dx = p0[0] - cur_x
                dy = p0[1] - cur_y
                d0 = np.sqrt(dx*dx + dy*dy)
                
                d1 = d0
                if use_reverse:
                    p0 = e['points'][-1]
                    dx = p0[0] - cur_x
                    dy = p0[1] - cur_y
                    d1 = np.sqrt(dx*dx + dy*dy)

                if d0 < dist:
                    dist = d0
                    closest = i
                elif d1 < dist and use_reverse:
                    dist = d1
                    closest = i
                    reverse = i

        if reverse is not None:
            reverse_list.append(reverse)

        if closest is None:
            return sort_list, reverse_list
        else:
            #~ print "added" , closest
            sort_list.append(closest)
            
        if len(sort_list) == len(elements):
            return sort_list, reverse_list
        else:
            return self.__sort_elements(elements, sort_list, reverse_list)
    
    def draw(self, data, optimize=False):
        #~ print 'draw-area', data.min_x, data.min_y, data.max_x, data.max_y
        width = data.max_x - data.min_x
        height = data.max_y - data.min_y
        #~ print 'draw', width, height
        #~ print 'offset', data.min_x, data.min_y
        
        for lyr in data.layers:
            color = lyr.color
            
            if not self.skip_level(color):
            
                self.level(color)
                
                elements = lyr.primitives
                
                if optimize > 0:
                    idx_list, reverse_list = self.__sort_elements(elements, use_reverse=(optimize>1) )
                else:
                    idx_list = range(len(elements))
                    reverse_list = []
                
                for i in idx_list:
                    e = elements[i]
                    t = e['type']
                    filled = False
                    if 'filled' in e:
                        filled = e['filled']
                        
                    if not filled:
                        if 'points' in e:
                            if i in reverse_list:
                                points = []
                                for pt in reversed(e['points']):
                                    points.append(pt)
                            else:
                                points = e['points']
                                    
                            self.draw_polyline(points, color)
                    else:
                        if t == 'rect':
                            p1 = e['points'][0]
                            p2 = e['points'][2]
                            self.fill_rect(p1[0], p1[1], p2[0], p2[1], color)                    
                        else:
                            print 'TODO (filled)', t
                    
                    #~ if t == 'line':
                        #~ pass
                    #~ elif t == 'polyline' or t == 'spline' or t == 'ellipse' or t == 'circle' or t == 'arc':
                        #~ self.draw_polyline(e['points'], color)
                    #~ elif t == 'circle':
                        #~ c = e['center']
                        #~ self.draw_circle( c[0], c[1], e['radius'], color)
                    #~ elif t == 'arc':
                        #~ c = e['center']
                        #~ self.draw_arc( c[0], c[1], e['radius'], e['start'], e['end'], color)
                    #elif t == 'ellipse':
                        #~ c = e['center']
                        #~ self.draw_ellipse(c[0], c[1], e['ratio'], e['major_axis'], e['start'], e['end'], color)
                    #~ elif t == 'rect':
                        #~ if e['filled']:
                            #~ p1 = e['points'][0]
                            #~ p2 = e['points'][2]
                            #~ self.fill_rect(p1[0], p1[1], p2[0], p2[1], color)
                        #~ else:
                            #~ pass
                    #~ else:
                        #~ print 'TODO', t
    
    def get_y_list_modulo(self, color, y1, y2):
        """
        Generate a list of Y for drawing a primitive between y1 and y2.
        If lines have to skipped, only the Y values that have to be drawn
        will be in the output list.
        
        :param color:
        :param y1:
        :param y2:
        :type color: uint8
        :type y1: float
        :type y2: float
        :returns: List of Y values that should be drawn
        :rtype: list
        """
        in_list = range(y1,y2)
        out_list = []
        mod = int(self.preset['skip']['mod'])
        tmp = self.preset['skip']['on']
        on = []
        for o in tmp:
            on.append( int(o) )
        
        for i in in_list:
            if self.interleave:
                v = (i+1) % mod
            else:
                v = i % mod
            if v in on:
                out_list.append(i)
        
        return out_list
    
    def __point_on_line(self, p1, p2, t):
        """ for future use """
        dx = p2[0] - p1[0]
        dy = p2[1] - p1[1]
        
        x = p1[0] + float(dx*t)
        y = p1[1] + float(dy*t)
        
        return (x, y)
    
    def __sub_bezier(self, points, t):
        """ for future use """
        sub_points = []
        
        p1 = points[0]
        for p2 in points[1:]:
            pt = self.__point_on_line(p1, p2, t)
            sub_points.append(pt)
            p1 = p2
        
        if len(sub_points) > 1:
            return self.__sub_bezier(sub_points, t)
        else:
            return sub_points
    
    def __bezier_points(self, points, t):
        """ for future use """
        if type(t) == list or type(t) == np.ndarray:
            pts = []
            for t1 in t:
                p = self.__sub_bezier(points, t1)
                pts = pts + p
            return pts
        else:
            return self.__sub_bezier(points, t)
    
    def fill_rect(self, x1, y1, x2, y2, color = 0):
        """
        Draw a rectangle out of hlines. Skip lines if needed.
        """
        y_list = self.skip_function[ self.preset['skip']['type'] ](color, y1, y2)
        
        for y in y_list:
            self.draw_hline(x1, x2, y, color)
    
    def draw_polyline(self, points, color = 0):
        #~ p0 = points[0]
        have_prev = False
        
        for p in points:
            p1 = ( int(p[0]), int(p[1]))
            #cv2.circle(self.dbg_img, p1, 2, 0)
            if have_prev:
                self.draw_line( p0[0], p0[1], p[0], p[1], color)
            
            p0 = p
            have_prev = True
    
    def draw_line(self, x1, y1, x2, y2, color = 0):
        raise NotImplementedError('"draw_line" function must be implemented')
    
    def draw_circle(self, x0, y0, r, color = 0, step = 10.0):
        """
        Draw an arc.
        """
        
        start = 0.0
        end = 360.0
        steps = int( 360.0 / step )

        have_prev = False
        
        for a in xrange(steps):
            angle = np.deg2rad(start + a*step)
            x2 = x0 + np.cos(angle)*r
            y2 = y0 + np.sin(angle)*r
            
            if have_prev:
                self.draw_line(x1,y1, x2,y2, color)

            x1 = x2
            y1 = y2
            have_prev = True
            
        if (start + (steps-1)*step) != end:
            angle = np.deg2rad(end)
            x2 = x0 + np.cos(angle)*r
            y2 = y0 + np.sin(angle)*r
            
            self.draw_line(x1,y1, x2,y2, color)
    
    def __ellipse_point(self, center, r1, r2, rotM, t):
        x1 = r1 * np.cos( np.radians(t) )
        y1 = r2 * np.sin( np.radians(t) )
        tmp = np.array([x1,y1])
        p1 = center + (tmp * rotM)
        return p1.A1[0], p1.A1[1]
    
    def draw_ellipse(self, x0, y0, ratio, axis, start, end, color = 0, step = 10.0):        
        # Get length of axis vector
        r1 = np.linalg.norm(axis)
        # Get second radius
        r2 = r1 * ratio
        
        # Get axis angle
        if axis[0] == 0:
            a = np.radians(90.0) 
        elif axis[1] == 0:
            a = np.radians(0.0) 
        else:
            a = np.arctan(axis[1] / axis[0])

        # Prepare rotation matrix
        center = np.array([x0,y0])
        c1 = np.cos(a)
        c2 = np.sin(a)
        rotM = np.matrix([
            [c1,c2],
            [-c2,c1]
        ])
        rotMCCW = np.matrix([
            [c1,-c2],
            [c2,c1]
        ])
        
        eye = np.matrix([ [1.0, 0.0], [0.0, 1.0] ])
        
        fix = 0
        if start > end:
            start += 180.0
            if start > 360.0:
                start -= 360.0
            end += 180.0
                   
        have_prev = False
        
        tl = np.arange(start, end, step)
        
        for t in tl:
            x2,y2 = self.__ellipse_point(center, r1, r2, rotM, t)

            if have_prev:
                self.draw_line(x1,y1, x2,y2)
            
            x1 = x2
            y1 = y2
            have_prev = True

        x2,y2 = self.__ellipse_point(center, r1, r2, rotM, end)
        self.draw_line(x1,y1, x2,y2)
        
    def draw_arc(self, x0, y0, r, start, end, color = 0, step = 10.0):
        """
        Draw an arc.
        """
        
        angle = end - start
        if angle < 0:
            angle += 360
        
        steps = int(abs(angle / step))

        have_prev = False
        
        for a in xrange(steps):
            angle = np.deg2rad(start + a*step)
            x2 = x0 + np.cos(angle)*r
            y2 = y0 + np.sin(angle)*r
            
            if have_prev:
                self.draw_line(x1,y1, x2,y2, color)

            x1 = x2
            y1 = y2
            have_prev = True
            
        if (start + (steps-1)*step) != end:
            angle = np.deg2rad(end)
            x2 = x0 + np.cos(angle)*r
            y2 = y0 + np.sin(angle)*r
            
            self.draw_line(x1,y1, x2,y2, color)
    
    def draw_hline(self, x1, x2, y, color = 0):
        """
        Draw a horizontal line.
        
        :param x1:    Start X
        :param x2:    End X
        :param y:     Start and end Y
        :param color: Line color
        """
        self.draw_line(x1, y, x2, y, color)
        
    def start(self):
        """
        Engraver initialization callback.
        """
        pass
    
    def skip_level(self, color):
        return False
    
    def level(self, color):
        """
        Level change callback.
        
        :param color: Level color
        :type color: uint8
        """
        pass
    
    def comment(self, comment):
        """
        Line comment callback.
        """
        pass
        
    def end(self):
        """
        Engraver finalization callback.
        """
        pass
