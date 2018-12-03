Ungerboeck Modules

This directory contains modules that read information from Ungerboeck, and creates
Drupal blocks to display the information. Currently, it contains 3 modules.
1) ungerboeck_helpers: This module contains support functions that are used by
   the other modules. This module should only be enabled because it's a dependancy
   of other modules.
2) ungerboeck_events: This is the general module for displaying events, and will be
   the one most often enabled. You can configure it to make as many blocks as you need,
   and each block can display it's own list of events
3) ungerboeck_servsafe: A special module to display ServSafe events on the foodsafey
   web site. It does special things with the Title of the Event.


## Installation

Currently, the installation needs some help. Currently, it requires the Real AES
(real_aes) module, which needs to be installed using composer. If you try to enable
underboeck_helpers, it will put encrypt and key in the modules directory, then composer
puts a second copy of them in the modules/custom folder. Therefore, install Real AES
before enablign the Ungerboeck modules.

You need to make a key, then use that key and Real AES to set up Encryption profile.
See: https://www.drupal.org/docs/8/modules/encrypt/general-drupal-8-encrypt-setup-and-recommendations


## Todo
1) Improve the installation of required modules
2) Put the machine name of the Encryption profile into the form, instead of hard coded.
   Ideally, this would give a dropdown list of available profiles.
3) Look at Tim's app to see if we can get more info to display
4) Add span tags so title and date can be formated with css


To use this module:
1) Enable the module: drush en ungerboeck_events
2) Clear cache: drush cc all
3) There are two configuration pages under Configuration -> Content Authoring
4) Configure the block Ungerboeck - Show Events 1 
5) Place the block


The page at https://registration.extension.iastate.edu/api/help/index will be a big
help in getting your search string. There is a pretty good search string being used
on the Foodsafety site as well.
