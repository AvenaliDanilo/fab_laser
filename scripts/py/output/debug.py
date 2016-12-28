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
import cv2,cv

# Import internal modules
from .common import EngraverOutput

class DebugEngraver(EngraverOutput):
    """
    Engrever Debug class. Stores the result in an image with
    resolution of one dot per pixel.
    """
    
    def __init__(self, filename, width, height, color = 0, dot_size = 0.1, invert = False, preset = {}):
        self.filename = filename
        self.dot_size = dot_size
        
        self.invert = invert
        
        if invert:
            color = 255 - color
        
        self.dbg_img = np.ones((int(height), int(width), 1), np.uint8)*color
        super(DebugEngraver, self).__init__(preset)
        
    def draw_hline(self, x1, x2, y, color = 0):
        tx1 = min(x1,x2)
        tx2 = max(x1,x2)
        if self.invert:
            color = 255 - color
        self.dbg_img[y:y+1, tx1:tx2] = color
    
    def draw_line(self, x1, y1, x2, y2, color = 0):
        if self.invert:
            color = 255 - color
        cv2.line(self.dbg_img, (int(x1),int(y1)), (int(x2),int(y2)), color)
    
    def draw_circle(self, x1, y1, r, color = 0):
        if self.invert:
            color = 255 - color
        cv2.circle(self.dbg_img, (int(x1), int(y1)), int(r), color)
    
    def end(self):
        print "Saving output to file '{0}'".format(self.filename)
        self.dbg_img = cv2.flip(self.dbg_img,0)
        cv2.imwrite(self.filename, self.dbg_img)
        
    def show(self):
        self.dbg_img = cv2.flip(self.dbg_img,0)
        cv2.imshow('image', self.dbg_img)
        cv2.waitKey(0)
        cv2.destroyAllWindows()
