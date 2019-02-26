# =============================================
# File:        gowRpiCamera.py
# Author:      Benny Saxen
# Date:        2019-02-25
# Description: application for running a picamera
# feedback: photo
# =============================================
import os
from gowLib import *
from picamera import PiCamera

#===================================================
def take_picture(co):
#===================================================
    camera = PiCamera()
    camera.resolution = (1280, 1024)
    camera.capture("temp.jpg")
    camera.close()

    api_host = lib_gowCreateMyUrl(co)
    headers = {'Content-Type' : 'image/jpeg'}
    image_url = 'temp.jpeg'
	
    img_file = urllib2.urlopen(image_url)
    response = requests.post(api_host, data=img_file.read(), headers=headers, verify=False)
	
    # copy image to server
    os.system("scp {0} {1}@{2}{3}{4}{5}".format("temp.jpg",
                                              co.image_user,
                                              co.image_url,
                                              co.image_path,
                                              co.image_prefix,
                                              co.image_name))

#===================================================
# Setup
#===================================================
confile = "gowrpicamera.conf"
lib_readConfiguration(confile,co)
lib_gowPublishMyStatic(co)
#===================================================
# Loop
#===================================================
while True:
    lib_gowIncreaseMyCounter(co,md)

    payload = '{}'
    msg = lib_gowPublishMyDynamic(co,md,payload)
    action = lib_common_action(co,msg)

    if action == 'photo':
        print 'take photo'
	message = 'counter:' + str(md.mycounter)
	lib_gowPublishMyLog(co, message)
        take_picture(co)

    print "sleep: " + str(co.myperiod) + " triggered: " + str(md.mycounter)
    time.sleep(float(co.myperiod))
#===================================================
# End of file
#===================================================
