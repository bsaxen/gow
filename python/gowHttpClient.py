# =============================================
# File: gowHttpClient.py
# Author: Benny Saxen
# Date: 2019-03-05
# Description:
# =============================================
from gowLib import *
#===================================================
# Setup
#===================================================
confile = "gowhttpclient.conf"
lib_readConfiguration(confile,co)
lib_gowPublishMyStatic(co)
#===================================================
# Loop
#===================================================
while True:
    lib_gowIncreaseMyCounter(co,dy)
    payload = '{}'
    msg = lib_gowPublishMyDynamic(co,dy,payload)
    lib_common_action(co,msg)
    message = 'counter:' + str(dy.mycounter)
    lib_gowPublishMyLog(co, message)
    print "sleep: " + str(co.myperiod) + " triggered: " + str(dy.mycounter)
    time.sleep(float(co.myperiod))
#===================================================
# End of file
#===================================================
