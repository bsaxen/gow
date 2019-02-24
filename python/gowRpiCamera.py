# =============================================
# File:        gowRpiCamera.py
# Author:      Benny Saxen
# Date:        2019-02-24
# Description: application for running a picamera
# feedback: photo
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
                                              p1.image_user,
                                              p1.image_url,
                                              p1.image_path,
                                              p1.image_prefix,
                                              p1.image_name))

#===================================================
# Setup
#===================================================
confile = "gowrpicamera.conf"
lib_readConfiguration(confile,co)
lib_gowPublishStatic(co)
#===================================================
# Loop
#===================================================
while True:
    lib_gowIncreaseCounter(co,md)

    payload = '{}'
    msg = lib_gowPublishDynamic(co,md,payload)
    action = lib_common_action(co,msg)

    if action == 'photo':
        print 'take photo'
	message = 'counter:' + str(md.mycounter)
	lib_gowPublishLog(co, message)
        take_picture(co)

    print "sleep: " + str(co.myperiod) + " triggered: " + str(md.mycounter)
    time.sleep(float(co.myperiod))
#===================================================
# End of file
#===================================================
