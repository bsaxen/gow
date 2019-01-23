# ==================================================
# File: gowTrigger.py
# Author: Benny Saxen
# Date: 2018-12-09
# Description:
# ==================================================
import schedule
import urllib
import urllib2
import time
import datetime
from lib import *

#===================================================
# Library
#===================================================

def job():
    global c1
    lib_placeOrder(c1, c1.c_topic1, c1.c_action1, c1.c_tag1 )
#===================================================
def placeOrder(top,act):
#===================================================
	url = conf_gs_url
	server = conf_server_name
	data = {}
  	data['do'] = 'action'
	data['topic'] = itop
	data['order'] = act
	values = urllib.urlencode(data)
	req = url + server + '?' + values
	try: response = urllib2.urlopen(req)
	except urllib2.URLError as e:
		print e.reason
	the_action = response.read()
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
    hello(c1)
    time.sleep(1)
#===================================================
# End of file
#===================================================
