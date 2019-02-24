# =============================================
# File: gowHttpClient.py
# Author: Benny Saxen
# Date: 2019-02-24
# Description:
# =============================================
from gowLib import *
#===================================================
# Setup
#===================================================
confile = "gowhttpclient.conf"
lib_readConfiguration(confile,co)
lib_gowPublishStatic(co)
#===================================================
# Loop
#===================================================
while True:

	md.mycounter += 1
	if md.mycounter > co.mywrap:
		md.mycounter = 1

	payload = '{}'
	msg = lib_gowPublishDynamic(co,md,payload)
	lib_common_action(co,msg)

	message = 'counter:' + str(md.mycounter)
	lib_gowPublishLog(co, message)

	print "sleep: " + str(co.myperiod) + " triggered: " + str(co.mycounter)
	time.sleep(float(co.myperiod))

#===================================================
# End of file
#===================================================
