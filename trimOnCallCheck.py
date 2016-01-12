#!C:/Python27/python.exe
# -*- coding: utf-8 -*-
import os, re, string, sys, getopt, struct, socket
from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

def main():
	#Sets OnCall Extension URL
	onCallExt = "http://my.halloo.com/ext/?extn=oncall&view=User%20Settings&tab=Forwarding"

	#Assigns driver to Firefox or Chrome (chromedriver must be installed in a folder specified on the path)
	#Firefox does not require an external application to function and works by default with Selenium installed
	driver = webdriver.Firefox() #webdriver.Chrome('C:\Python27\chromedriver.exe')

	#Loads Halloo login page (to ensure you actually log in)
	driver.get("https://secure1.halloo.com/sign-in/")
	if driver.title == "":
		driver.quit()
		exit()
	else:
		#print driver.title

		#Login fields for Halloo. Can use just email and password.
		#If email not given, ucomp must be sleepex
		#nameInput = driver.find_element_by_name("uname")
		#nameInput.send_keys("Your Extension Name/User Name")

		#Finds login fields on Halloo Login page
		compInput = driver.find_element_by_name("ucomp")
		passInput = driver.find_element_by_name("upass")

		#Types login information on page
		compInput.send_keys("") #Insert your email address
		passInput.send_keys("") #Insert your pin code

		#Submits information
		passInput.submit()

		#onCallFile is location of the onCall file on the local machine
		#phpTransferFile is location of the file used to transfer from Python to PHP on the local machine
		onCallFile = "Ext\oncall"
		phpTransferFile = "Ext\phptransfer"

		#print driver.title
		if "Sign-In" in driver.title:
			#Exits if statement because login failed to Halloo
			#print "Login to Halloo failed. Check login information" + "<br>"
			#Quits selenium driver
			driver.quit()
		else:
			#Loads onCallExt from above
			driver.get(onCallExt)
			
			#Prints OnCall first and last name to confirm information
			#print ("Forwarding information for extension: " + driver.find_element_by_name("firstname").get_attribute("value") + " " + driver.find_element_by_name("lastname").get_attribute("value")) + "<br>"
			#Captures forwarding extensions to array for safekeeping
			arrayList = driver.find_elements_by_xpath("//*[contains(@name, 'fwd_')]")
			#print arrayList[2].get_attribute("value").rstrip()
			#sys.stdout.write(arrayList[2].get_attribute("value").rstrip())
			transferFile = open(phpTransferFile,"w+")
			transferFile.write(arrayList[2].get_attribute("value").rstrip())
			transferFile.close()
			driver.quit()
			exit()

if __name__ == '__main__':
    main()