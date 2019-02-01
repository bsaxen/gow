# ==================================================
# File: gowTrigger.py
# Author: Benny Saxen
# Date: 2019-01-31
# Description:
# Trigger an action or a publication
# ==================================================
import schedule
from gowLib import *
counter = 0
#===================================================
# Functions
#===================================================

#===================================================
def jobStatic():
    global c1

    # Action
    #lib_placeOrder(c1, c1.c_topic1, c1.c_action1)
    # Publication
    lib_publish_static(c1, c1.c_topic1 )

#===================================================
def jobDynamic():
    global c1
    global counter
    counter += 1
    if counter > r1.c_wrap:
        counter = 1
    # Action
    #lib_placeOrder(c1, c1.c_topic1, c1.c_action1)
    # Publication
    lib_publish_dynamic(c1, c1.c_topic1, c1.c_payload1, counter )
#===================================================
# Setup
#===================================================
print "======== gowTrigger version 2019-01-30 =========="
schedule.every(10).seconds.do(jobDynamic)
schedule.every(10).minutes.do(jobStatic)
#schedule.every().hour.do(job)
#schedule.every().day.at("10:30").do(job)
#schedule.every().monday.do(job)
#schedule.every().wednesday.at("13:15").do(job)
c1 = configuration()
confile = "gowtrigger.conf"
print "Read configuration"
lib_readConfiguration(confile,c1)
lib_publish_static(c1, c1.c_topic1 )
counter = 0
#===================================================
# Loop
#===================================================
while True:
    counter += 1
    if counter > conf_wrap:
   	    counter = 1
    lib_publish_dynamic(c1, c1.c_topic1, c1.c_payload1, counter )
    print "sleep: " + str(c1.c_period) + " triggered: " + str(counter)
    schedule.run_pending()
    time.sleep(float(c1.c_period))
#===================================================
# End of file
#===================================================
