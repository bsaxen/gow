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
#===================================================
# Configuration
#===================================================
conf_gs_url        = 'http://127.0.0.1/git/gow/'
conf_server_name   = 'gowServer.php'
conf_period        = 10
conf_hw            = 'python'
conf_wrap          = 999999

topic1  = 'test/temperature/outdoor/1'
action1 = 'do something'
#===================================================
# Library
#===================================================
def job():
    placeOrder(topic1,action1)
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
#schedule.every(1).minutes.do(job)
#schedule.every().hour.do(job)
#schedule.every().day.at("10:30").do(job)
#schedule.every().monday.do(job)
#schedule.every().wednesday.at("13:15").do(job)

while True:
    schedule.run_pending()
    time.sleep(1)
#===================================================
# End of file
#===================================================
