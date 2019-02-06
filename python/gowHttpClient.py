# =============================================
# File: gowHttpClient.py
# Author: Benny Saxen
# Date: 2019-02-06
# Description:
# =============================================
from gowLib import *
#===================================================
# Setup
#===================================================
print "======== gowHttpClient version 2019-02-01 =========="
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
	
	print "sleep: " + str(c1.c_period) + " triggered: " + str(d1.no)
	time.sleep(float(c1.c_period))

#===================================================
# End of file
#===================================================
