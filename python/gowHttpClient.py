# =============================================
# File: gowHttpClient.py
# Author: Benny Saxen
# Date: 2019-02-25
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
    lib_gowIncreaseMyCounter(co,md)

	payload = '{}'
	msg = lib_gowPublishMyDynamic(co,md,payload)
	lib_common_action(co,msg)

	message = 'counter:' + str(md.mycounter)
	lib_gowPublishMyLog(co, message)

	print "sleep: " + str(co.myperiod) + " triggered: " + str(co.mycounter)
	time.sleep(float(co.myperiod))

#===================================================
# End of file
#===================================================
