# ==================================================
# File: gowTrigger.py
# Author: Benny Saxen
# Date: 2019-01-23
# Description:
# ==================================================
import schedule
from lib import *

#===================================================
# Functions
#===================================================
def job():
    global c1
    lib_placeOrder(c1, c1.c_topic1, c1.c_action1, c1.c_tag1 )
#===================================================
# Main
#===================================================
schedule.every(1).minutes.do(job)
#schedule.every().hour.do(job)
#schedule.every().day.at("10:30").do(job)
#schedule.every().monday.do(job)
#schedule.every().wednesday.at("13:15").do(job)
c1 = configuration()
confile = "gowtrigger.conf"
lib_readConfiguration(confile,c1)
while True:
    schedule.run_pending()
    time.sleep(1)
#===================================================
# End of file
#===================================================
