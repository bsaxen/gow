# ==================================================
# File: gowTrigger.py
# Author: Benny Saxen
# Date: 2019-01-30
# Description:
# Trigger an action or a publication
# ==================================================
import schedule
from lib import *
counter = 0
#===================================================
# Functions
#===================================================

#===================================================
def jobStatic():
    global r1

    # Action
    #lib_placeOrder(c1, c1.c_topic1, c1.c_action1)
    # Publication
    lib_publish_static(r1, r1.c_topic1 )

#===================================================
def jobDynamic():
    global r1
    global counter
    counter += 1
    if counter > r1.c_wrap:
        counter = 1
    # Action
    #lib_placeOrder(c1, c1.c_topic1, c1.c_action1)
    # Publication
    lib_publish_dynamic(r1, r1.c_topic1, r1.c_payload1, counter )
#===================================================
# Main
#===================================================
print "======== gowTrigger version 2019-01-30 =========="
schedule.every(10).seconds.do(jobDynamic)
schedule.every(10).minutes.do(jobStatic)
#schedule.every().hour.do(job)
#schedule.every().day.at("10:30").do(job)
#schedule.every().monday.do(job)
#schedule.every().wednesday.at("13:15").do(job)
r1 = configuration()
confile = "gowtrigger.conf"
print "Read configuration"
lib_readConfiguration(confile,r1)
counter = 0
print "Loop"
while True:
    print "sleep: " + str(r1.c_period) + " triggered: " + str(counter)
    schedule.run_pending()
    time.sleep(float(r1.c_period))
#===================================================
# End of file
#===================================================
