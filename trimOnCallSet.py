#!C:/Python27/python.exe
# -*- coding: utf-8 -*-
import os, re, string, sys, getopt
from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

#re is the module for regex

#Gets username in operating system. Not necessary
#import getpass
#print(getpass.getuser() + "\n")
#import platform
#print(platform.python_version() + "\n")

cellService = ""
phoneNum = ""

#pulled from: http://code.activestate.com/recipes/410692/
#example by Brian Beck
class switch(object):
    def __init__(self, value):
        self.value = value
        self.fall = False

    def __iter__(self):
        """Return the match method once, then stop"""
        yield self.match
        raise StopIteration
    
    def match(self, *args):
        """Indicate whether or not to enter a case suite"""
        if self.fall or not args:
            return True
        elif self.value in args: # changed for v1.5, see below
            self.fall = True
            return True
        else:
            return False

def errPrint():
	print '-h, --help<br>'
	print '		prints this help command<br><br>'
	print '-p, --phone=<br>'
	print '		input phone number to change phones to<br><br>'
	print '-s, --service=<br>'
	print '		input cell service to change text notifications for voicemails<br><br>'

def printExt():
	#Loops through extension fields on currently open page to print current phone numbers for Home, Office, and Mobile
	for i, w in enumerate(driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")):
		if i == 0:
			sys.stdout.write("H: " + w.get_attribute("value") + "<br>")
		elif i == 1:
			sys.stdout.write("O: " + w.get_attribute("value") + "<br>")
		else:
			sys.stdout.write("M: " + w.get_attribute("value") + "<br>")
			sys.stdout.write("" + "<br>")
			
def setExt(numToSet):
	#Takes input of Phone Number, clears current extension fields, sets new extension from input, and submits
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[0].clear()
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[0].send_keys(numToSet)
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[1].clear()
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[1].send_keys(numToSet)
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[2].clear()
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[2].send_keys(numToSet)
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[2].submit()

def setCEmail(serviceEmail):
	driver.find_element_by_id("cMemail").clear()
	driver.find_element_by_id("cMemail").send_keys(serviceEmail)
	driver.find_element_by_id("cMemail").submit()

def cellularConvert(aNumber, cProvider):
	aNumber = aNumber[1:]
	for case in switch(cProvider):
		if case('att'):
			aNumber = aNumber[1:].replace("-","")
			aNumber = aNumber + "@txt.att.net"
			return aNumber
		if case('sprint'):
			aNumber = aNumber.replace("-","")
			aNumber = aNumber + "@messaging.sprintpcs.com"
			return aNumber
		if case('verizon'):
			aNumber = aNumber.replace("-","")
			aNumber = aNumber + "@vtext.com"
			return aNumber
		if case('tmobile'):
			aNumber = aNumber.replace("-","")
			aNumber = aNumber + "@tmomail.net"
			return aNumber
		if case(): # default, could also just omit condition or 'if True'
			return "Not a valid cellular service at this time"
			# No need to break here, it'll stop anyway

def selFunc(phone,cEmail):
	#On Call Extension Forwarding URL
	onCallExt = "http://my.halloo.com/ext/?extn=oncall&view=User%20Settings&tab=Forwarding"
	onCallExtGen = "http://my.halloo.com/ext/?extn=oncall&view=User%20Settings&tab=General"
	#TSEmergency Extension Forwarding URL
	tsEmerExt = "http://my.halloo.com/ext/?extn=TSEmergency&view=User%20Settings&tab=Forwarding"
	tsEmerExtGen = "http://my.halloo.com/ext/?extn=TSEmergency&view=User%20Settings&tab=General"

	#Assigns driver to Firefox or Chrome (chromedriver must be installed in a folder specified on the path)
	#Firefox does not require an external application to function and works by default with Selenium installed
	global driver 
	driver = webdriver.Firefox()

	driver.get("https://secure1.halloo.com/sign-in/")
	if driver.title == "":
		driver.quit()
	else:
		#Login fields for Halloo. Can use just email and password.
		#If email not given, ucomp must be sleepex
		#nameInput = driver.find_element_by_name("uname")
		#nameInput.send_keys("Your Extension Name/User Name")

		compInput = driver.find_element_by_name("ucomp")
		passInput = driver.find_element_by_name("upass")

		#Types login information on page
		compInput.send_keys("") #Insert your email address
		passInput.send_keys("") #Insert your pin code
		passInput.submit()

		if "Sign-In" in driver.title:
			#Exits if statement because login failed to Halloo
			sys.stdout.write("Login to Halloo failed. Check login information")
		else:			
			driver.get(onCallExt)
			setExt(phone)
			driver.get(onCallExtGen)
			setCEmail(cEmail)
			
			driver.get(tsEmerExt)
			setExt(phone)
			driver.get(tsEmerExtGen)
			setCEmail(cEmail)
		driver.quit()

def main(argv):
	global cellService
	global phoneNum
	try:
		opts, args = getopt.getopt(argv,"hp:s:", ["help", "phone=", "service="])
		if not opts:
			# Return proper usage of script if in error
			errPrint()
	except getopt.GetoptError:
		# Return proper usage of script if in error
		errPrint()
		sys.exit(2)
	for opt, arg in opts:
		if opt in ("-h", "--help"):
			# Return proper usage of script if in error
			errPrint()
			sys.exit(2)
		elif opt in ("-s", "--service"):
			cellService = arg;
		elif opt in ("-p", "--phone"):
			phoneNum = arg;
	
	cellConvert = cellularConvert(phoneNum, cellService)
	if cellConvert == "Not a valid cellular service at this time":
		print "There was an error setting the phone number"
		sys.exit(2)
	selFunc(phoneNum, cellConvert)
if __name__ == '__main__':
    main(sys.argv[1:])