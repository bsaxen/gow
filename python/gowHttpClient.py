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

co = Configuration()
ds = Datastream()
md = ModuleDynamic()

confile = "gowhttpclient.conf"
print "Read configuration"
lib_readConfiguration(confile,co)

mytopic = co.mytopic
lib_gowPublishStatic(co)

co.mycounter = 0
#===================================================
# Loop
#===================================================
while True:

	co.mycounter += 1
	if co.mycounter > co.mywrap:
		co.mycounter = 1


	payload = '{}'
	msg = lib_gowPublishDynamic(co,md,payload)
	lib_common_action(co,msg)
	message = 'counter:' + str(co.mycounter)
	lib_gowPublishLog(co, message)

	print "sleep: " + str(co.myperiod) + " triggered: " + str(co.mycounter)
	time.sleep(float(co.myperiod))

#===================================================
# End of file
#===================================================
