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

# Import standard python module
import time

# Import external modules
import numpy as np

# Import internal modules
from .common import EngraverOutput

class LaserEngraver(EngraverOutput):
    """
    Laser engraver gcode generator class.
    """
    
    def __init__(self, filename, scale, preset, statistics=False):
        self.filename = filename
        self.scale = scale
        self.cur_x = 0.0
        self.cur_y = 0.0
        self.laser_pwm = 0
        super(LaserEngraver, self).__init__(preset)
        
        self.pwm_function = {
            'const' : self.get_pwm_value_const,
            'linear' : self.get_pwm_value_linear
        }
        
        self.speed_function = {
            'const' : self.get_burn_speed_value_const,
            'linear' : self.get_burn_speed_value_linear,
            'travel' : self.get_travel_speed_value_const
        }
        
        self.get_pwm_value = self.pwm_function[ preset['pwm']['type'] ]
        self.get_burn_speed = self.speed_function[ preset['speed']['type'] ]
        self.get_travel_speed = self.speed_function[ 'travel' ]
        
        self.laser_is_on = False
        self.off_during_travel = False
        if 'off_during_travel' in preset['pwm']:
            self.off_during_travel = preset['pwm']['off_during_travel']
        
        self.statistics = statistics
        self.stats = {
            'travel_length' : 0.0,
            'burn_length' : 0.0,
            'travel_time' : 0.0,
            'burn_time' : 0.0
        }

        self.pwm_gocde = 'M4 S{0}\r\n'
        #~ self.pwm_gocde = 'M4 S{0}\r\nM400\r\n'
        #~ self.fd.write('M400

    def comment(self, comment):
        """
        Add comment to the gcode output.
        :param comment: Comment to be added.
        
        """
        self.fd.write(";{0}\r\n".format(comment))
    
    def get_pwm_value_const(self, color):
        """
        Returns constant PWM value.
        
        :returns: PWM value
        :rtype: uint8
        """
        return int(self.preset['pwm']['value'])
        
    def get_pwm_value_linear(self, color):
        """
        Returns PWM value using a linear function to convert color
        to PWM value.
        
        :param color:
        :type color: uint8
        :returns: PWM value.
        :rtype: uint8
        """
        x_min = int(self.preset['pwm']['in_min'])
        x_max = int(self.preset['pwm']['in_max'])
        y_min = int(self.preset['pwm']['out_min'])
        y_max = int(self.preset['pwm']['out_max'])
        
        dx = float(x_max - x_min)
        dy = float(y_max - y_min)
        k = dy / dx

        y = 0

        if color >= x_min and color < x_max:
            y = y_min + (color-x_min) * k
            
        if color >= x_max:
            y = y_max

        return int(y)
        
    def get_burn_speed_value_const(self, color):
        """
        Returns constant burning speed.
        
        :returns: Burn speed value.
        :rtype: uint8
        """
        return int(self.preset['speed']['burn'])
        
    def get_burn_speed_value_linear(self, color):
        """
        Returns burning speed using a linear function to convert color
        to speed value.
        
        :param color:
        :type color: uint8
        :returns: Burn speed value.
        :rtype: int
        """
        x_min = int(self.preset['speed']['in_min'])
        x_max = int(self.preset['speed']['in_max'])
        y_min = int(self.preset['speed']['out_min'])
        y_max = int(self.preset['speed']['out_max'])
        
        dx = float(x_max - x_min)
        dy = float(y_max - y_min)
        k = dy / dx

        y = 0

        if color >= x_min and color < x_max:
            y = y_min + (color-x_min) * k
            
        if color >= x_max:
            y = y_max

        return int(y)
                
    def get_travel_speed_value_const(self, color = 0):
        """
        Returns constant Travel speed.
        
        :returns: Travel speed.
        :rtype: uint8
        """
        return self.preset['speed']['travel']
    
    def level(self, color):
        """
        Level change setup gcode.
        """
        pwm = self.get_pwm_value(color)
        self.laser_pwm = pwm
        speed = self.get_burn_speed(color)
        print "Color {0} => PWM: {1}, Speed: {2}".format(color, pwm, speed)
        self.comment(' Applying PWM value {0}'.format(pwm) )
        self.fd.write(self.pwm_gocde.format(pwm))
        self.fd.write('M400 ;Make sure all previous moves are finished\r\n')
    
    def skip_level(self, color):
        pwm = self.get_pwm_value(color)
        return (pwm == 0)
    
    def start(self):
        """
        Engraver start function.
        """
        self.fd = open(self.filename, 'w')
        self.add_start_code()

    def add_start_code(self):
        """
        Engraver start code.
        """
        now = time.strftime("%c")
        self.fd.write("""\
;FABtotum laser engraving, coded on {0}
G4 S1 ;1 millisecond pause to buffer the bep bep
M450 S2 ; Activate laser module
M793 S4 ;set laser head
M728 ;FAB bep bep
G90 ; absolute mode
G4 S1 ;1 second pause to reach the printer (run fast)
G1 F10000 ;Set travel speed
M107
""".format(now))
        
    def add_engrave_move(self, x, y, color):
        """
        Add gcode for an engraving move.
        
        :param x: Target X.
        :param y: Target y.
        :param color: Engrave color.
        :type x: float
        :type y: float
        :type color: uint8
        """
        
        # mm/min
        feed = self.get_burn_speed(color)
        pwm = self.get_pwm_value(color)
        
        if self.statistics:
            dx = self.cur_x - x
            dy = self.cur_y - y
            travel = np.sqrt(dx*dx + dy*dy)
            self.stats['burn_length'] += travel
            self.stats['burn_time'] += travel / (feed/60)
        
        #~ if not self.laser_is_on:
            #~ self.laser_is_on = True
        if self.off_during_travel:
            self.fd.write('M400\r\n')
            self.fd.write(self.pwm_gocde.format(pwm))
            
            
        self.fd.write("G1 X{0} Y{1} F{2}\r\n".format(x*self.scale[0], y*self.scale[1], feed) )
        self.cur_x = x
        self.cur_y = y
        
    def add_travel_move(self, x, y):
        """
        Add gcode for a travel move.
        
        :param x: Target X
        :param y: Target y
        :type x: float
        :type y: float
        """
        
        # mm/min
        feed = self.get_travel_speed() 
        
        if x != self.cur_x or y != self.cur_y:
            
            #~ if self.off_during_travel and self.laser_is_on:
                #~ self.laser_is_on = False
                #~ self.fd.write(self.pwm_gocde.format(0))
                
            if self.off_during_travel:
                self.fd.write('M400\r\n')
                self.fd.write(self.pwm_gocde.format(0))
                
            
            if self.statistics:
                dx = self.cur_x - x
                dy = self.cur_y - y
                travel = np.sqrt(dx*dx + dy*dy)
                self.stats['travel_length'] += travel
                self.stats['travel_time'] += travel / (feed/60)
            
            self.fd.write("G0 X{0} Y{1} F{2}\r\n".format(x*self.scale[0], y*self.scale[1], feed) )
            self.cur_x = x
            self.cur_y = y
        
    def end(self):
        """
        Engraver end function.
        """
        self.add_end_code()
        self.fd.close()
        
    def add_end_code(self):
        """
        Shutdown code.
        """
        self.fd.write("""\
M400 ;Wait for all moves to finish
M728 ;FAB bep bep (end print)
G4 S1 ;pause
M5 ;shutdown
""")
    
    def draw_line(self, x1, y1, x2, y2, color = 0):
        self.add_travel_move(x1, y1)
        self.add_engrave_move(x2, y2, color)
