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

# Import external modules

# Import internal modules
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher
import fabtotum.fabui.macros.general as general_macros
import fabtotum.fabui.macros.printing as print_macros

# Set up message catalog access
tr = gettext.translation('mill', 'locale', fallback=True)
_ = tr.ugettext

################################################################################

class Application(GCodePusher):
    """
    Milling application.
    """
    
    def __init__(self, log_trace, monitor_file, standalone = False, autolevel = False, finalize = True):
        super(Application, self).__init__(log_trace, monitor_file, use_stdout=standalone )
        self.standalone = standalone
        self.autolevel = autolevel
        self.finalize = finalize
        
    def progress_callback(self, percentage):
        print "Progress", percentage
    
    def task_finalize(self):                                                                                                                                                                                                                                                                                                                                                                  
        if self.standalone or self.finalize:
            if self.is_aborted():
                self.set_task_status(GCodePusher.TASK_ABORTING)
            else:
                self.set_task_status(GCodePusher.TASK_COMPLETING)
            
            #~ self.exec_macro("end_subtractive")
            
            if self.is_aborted():
                self.set_task_status(GCodePusher.TASK_ABORTED)
            else:
                self.set_task_status(GCodePusher.TASK_COMPLETED)
        
        self.stop()
    
    def first_move_callback(self):
        self.trace( _("Engraving STARTED") )
        
        with self.monitor_lock:
            self.print_stats['first_move'] = True
            self.set_task_status(GCodePusher.TASK_RUNNING)
            self.update_monitor_file()

    def file_done_callback(self):   
        self.task_finalize()
        
    def state_change_callback(self, state):
        if state == 'paused':
            self.trace( _("Engraving PAUSED") )
        if state == 'resumed':
            self.trace( _("Engraving RESUMED") )
        if state == 'aborted':
            self.trace( _("Engraving ABORTED") )
        
    def run(self, task_id, gcode_file):
        """
        Run the print.
        
        :param gcode_file: GCode file containing print commands.
        :param task_id: Task ID
        :type gcode_file: string
        :type task_id: int
        """

        self.prepare_task(task_id, task_type='fab_laser', task_controller='plugin', gcode_file=gcode_file)
        self.set_task_status(GCodePusher.TASK_RUNNING)
        
        #if self.standalone:
            #~ self.exec_macro("check_pre_print")
            #self.exec_macro("start_subtractive")
        
        self.send_file(gcode_file)
        
        self.trace( _("Engraving Initialized.") )

def main():
    config = ConfigService()

    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-T", "--task-id",     help=_("Task ID."),      default=0)
    parser.add_argument("-F", "--file-name",   help=_("File name."),    required=True)

    # GET ARGUMENTS
    args = parser.parse_args()

    # INIT VARs
    gcode_file      = args.file_name     # GCODE FILE
    task_id         = args.task_id
    if task_id == 0:
        standalone  = True
    else:
        standalone  = False
        
    autolevel       = False
    monitor_file    = config.get('general', 'task_monitor')      # TASK MONITOR FILE (write stats & task info, es: temperatures, speed, etc
    log_trace       = config.get('general', 'trace')        # TASK TRACE FILE 

    app = Application(log_trace, monitor_file, standalone, autolevel)

    app.run(task_id, gcode_file)
    app.loop()

if __name__ == "__main__":
    main()
