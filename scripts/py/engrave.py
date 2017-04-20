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

# Import external modules

# Import internal modules
from fabtotum.utils.translation import _, setLanguage
from fabtotum.fabui.config  import ConfigService
from fabtotum.fabui.gpusher import GCodePusher
import fabtotum.fabui.macros.general as general_macros
import fabtotum.fabui.macros.printing as print_macros

################################################################################

class Application(GCodePusher):
    """
    Milling application.
    """
    
    def __init__(self, standalone = False):
        super(Application, self).__init__(use_stdout=standalone)
        self.standalone = standalone
    
    def task_finalize(self):
        if self.is_aborted():
            self.set_task_status(GCodePusher.TASK_ABORTING)
        else:
            self.set_task_status(GCodePusher.TASK_COMPLETING)
        
        if self.is_aborted():
            self.exec_macro("end_engraving_aborted")
            self.set_task_status(GCodePusher.TASK_ABORTED)
        else:
            self.exec_macro("end_engraving")
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
            self.trace( _("Please wait until the buffered moves in totumduino are finished") )
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

        self.prepare_task(task_id, task_type='laser', gcode_file=gcode_file)
        self.set_task_status(GCodePusher.TASK_RUNNING)
        
        if self.standalone:
            self.exec_macro("start_engraving")
        
        self.send_file(gcode_file)
        
        self.trace( _("Engraving Initialized.") )

def main():
    config = ConfigService()

    # SETTING EXPECTED ARGUMENTS
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-T", "--task-id",     help=_("Task ID."),      default=0)
    parser.add_argument("-F", "--file-name",   help=_("File name."),    required=True)
    parser.add_argument("--lang",              help="Output language", default='en_US.UTF-8' )
    parser.add_argument("--email",             help="Send an email on task finish", action='store_true', default=False)
    parser.add_argument("--shutdown",          help="Shutdown on task finish", action='store_true', default=False )
    
    # GET ARGUMENTS
    args = parser.parse_args()

    # INIT VARs
    gcode_file      = args.file_name     # GCODE FILE
    task_id         = args.task_id
    lang            = args.lang
    send_email      = bool(args.email)
    auto_shutdown   = bool(args.shutdown)
    
    if task_id == 0:
        standalone  = True
    else:
        standalone  = False

    app = Application(standalone)

    app.run(task_id, gcode_file)
    app.loop()

if __name__ == "__main__":
    main()
