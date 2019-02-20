# =============================================
# File:        gowRpiCamera.py
# Author:      Benny Saxen
# Date:        2019-02-20
# Description: application for running a picamera
# =============================================
import os
from gowLib import *
from picamera import PiCamera

#===================================================
def take_picture(p1):
#===================================================
    camera = PiCamera()
    camera.resolution = (1280, 1024)
    camera.capture("temp.jpg")
    camera.close()
    # copy image to server
    os.system("scp {0} {1}@{2}{3}{4}{5}".format("temp.jpg",
                                              p1.c_image_user,
                                              p1.c_image_url,
                                              p1.c_image_path,
                                              p1.c_image_prefix,
                                              p1.c_image_name))

#===================================================
# Setup
#===================================================
print "======== gowRpiCamera version 2019-01-31 =========="
counter = 0
c1 = Configuration()
confile = "gowrpicamera.conf"
print "Read configuration"
lib_readConfiguration(confile,c1)
lib_publish_static(c1, c1.c_topic1 )
#===================================================
# Loop
#===================================================
while True:

   counter += 1
   if counter > conf_wrap:
	counter = 1

   lib_publish_dynamic(c1, c1.c_topic1, c1.c_payload1, counter )
   print "sleep: " + str(c1.c_period) + " triggered: " + str(counter)
   time.sleep(float(c1.c_period))
#===================================================
# End of file
#===================================================
