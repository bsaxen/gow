# =============================================
# File: gowHttpClient.py
# Author: Benny Saxen
# Date: 2019-02-09
# Description:
# =============================================
from gowLib import *
#===================================================
# Setup
#===================================================
print "======== gowHttpClient version 2019-02-07 =========="
c1 = configuration()
d1 = datastream()
action = 2
confile = "gowhttpclient.conf"
print "Read configuration"
lib_readConfiguration(confile,c1)
lib_publish_static(c1, c1.c_topic1, action )
d1.no = 0
#===================================================
# Loop
#===================================================
while True:
	d1.no += 1
	if d1.no > c1.c_wrap:
		d1.no = 1

	msg = lib_publish_dynamic(c1, c1.c_topic1, c1.c_payload1, d1.no, action)
	lib_common_action(c1,msg)

	print "sleep: " + str(c1.c_period) + " triggered: " + str(d1.no)
	time.sleep(float(c1.c_period))
	error = 1
	if (error == 1):
		lib_publish_log(c1, c1.c_topic1, "test error message")

#===================================================
# End of file
#===================================================
