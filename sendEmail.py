#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os, sys, getopt, smtplib, logging, datetime

if(os.path.isdir("LOGS") == False):
  os.makedirs("LOGS")
logFileName = os.path.normpath("LOGS\sendMail") + datetime.datetime.now().strftime("%y%m%d%H%M%S") + ".LOG"
logging.basicConfig(format='%(levelname)s: %(message)s', filename=logFileName,level=logging.INFO)

msgSetup = ["","",""]

def errPrint():
  print '-h, --help'
  print ' prints this help command'
  
def arrayAddition(arg,i):
  global msgSetup
  if i == 1:
    logging.info("Adding '" + arg + "' (recipient) to the list.")
  elif i == 0:
    logging.info("Adding '" + arg + "' (message) to the list.")
  elif i == 2:
    logging.info("Adding '" + arg + "' (title) to the list.")
  msgSetup[i]=arg
  return

def sendingMail():
  try:
    global msgSetup
    # Import the email modules we'll need
    from email.mime.text import MIMEText
    msg = MIMEText(msgSetup[0])

    # me == the sender's email address
    # you == the recipient's email address
    if msgSetup[2] == "":
      logging.warning("No title included. Inserting text 'No Subject' instead.")
      msg['Subject'] = "No Subject"
    else:
      msg['Subject'] = msgSetup[2]
    msg['From'] = "" #Put from email address
    msg['To'] = msgSetup[1]

    # Send the message via our own SMTP server, but don't include the
    # envelope header.
    s = smtplib.SMTP('smtp.gmail.com', 587)
    s.starttls()
    s.login(msg['From'], "") #Put application specific password
    s.sendmail(msg['From'], msg['To'], msg.as_string())
    s.quit()
    logging.info("Email sent.")
  except smtplib.SMTPRecipientsRefused, e:
    logging.error(e)
    sys.exit(1)
  except:
    logging.error("Unexpected error")
    print(sys.exc_info()[0])
    sys.exit(1)
  
def main(argv):
  global msgSetup
  logging.info("Starting to parse arguments")
  try:
    opts, args = getopt.getopt(argv,"hr:m:t:", ["help", "recipient=", "msg=", "title="])
    if not opts:
      # Return proper usage of script if in error
      logging.warning("No arguments provided. Printing help message and closing.")
      errPrint()
  except getopt.GetoptError:
    # Return proper usage of script if in error
    logging.warning("No arguments provided. Printing help message and closing.")
    errPrint()
    sys.exit(2)
  for opt, arg in opts:
    if opt in ("-h", "--help"):
      # Return proper usage of script if in error
      logging.info("Help message requested. Printing help message and closing.")
      errPrint()
      sys.exit(2)
    elif opt in ("-r", "--recipient"):
      arrayAddition(arg,1)
    elif opt in ("-m", "--msg"):
      arrayAddition(arg,0)
    elif opt in ("-t", "--title"):
      arrayAddition(arg,2)

  logging.info("Attempting to send email.")
  sendingMail()
  
if __name__ == '__main__':
    main(sys.argv[1:])