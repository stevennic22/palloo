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

def errPrint():
	print '-h, --help<br>'
	print '		prints this help command<br><br>'
	print '-p, --phone=<br>'
	print '		input phone number to change phones to<br><br>'

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
			
def setExt(phoneNum):
	#Takes input of Phone Number, clears current extension fields, sets new extension from input, and submits
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[0].clear()
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[0].send_keys(phoneNum)
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[1].clear()
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[1].send_keys(phoneNum)
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[2].clear()
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[2].send_keys(phoneNum)
	driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")[2].submit()

def selFunc(phoneNum):
	#On Call Extension Forwarding URL
	onCallExt = "http://my.halloo.com/ext/?extn=oncall&view=User%20Settings&tab=Forwarding"
	#TSEmergency Extension Forwarding URL
	tsEmerExt = "http://my.halloo.com/ext/?extn=TSEmergency&view=User%20Settings&tab=Forwarding"

	#Assigns driver to Firefox or Chrome (chromedriver must be installed in a folder specified on the path)
	#Firefox does not require an external application to function and works by default with Selenium installed
	global driver 
	driver = webdriver.Firefox()

	driver.get("https://secure1.halloo.com/sign-in/")

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
		setExt(phoneNum)
		driver.get(tsEmerExt)
		setExt(phoneNum)
	driver.quit()

def main(argv):
	try:
		opts, args = getopt.getopt(argv,"hp:", ["help", "phone="])
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
		elif opt in ("-p", "--phone"):
			phoneNum = arg;
			selFunc(phoneNum)
if __name__ == '__main__':
    main(sys.argv[1:])