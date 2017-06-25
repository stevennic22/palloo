#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os, sys, getopt, smtplib, logging, datetime

logFileDir = "LOGS"

if(os.path.isdir(logFileDir) == False):
  os.makedirs(logFileDir)
logFileName = os.path.normpath(logFileDir + os.sep + os.path.splitext(__file__)[0] + datetime.datetime.now().strftime("%y%m%d%H%M%S") + ".LOG")

log = logging.getLogger(__name__)
log.setLevel(logging.INFO)
handler = logging.FileHandler(logFileName)
formatter = logging.Formatter("[%(asctime)s] '%(levelname)s': {%(message)s}")
handler.setFormatter(formatter)
log.addHandler(handler)

def errPrint(errorToLog):
  log.error(errorToLog)

  errorText = '''
-h, --help
  prints this help command

The following arguments must be used together to properly send an email.

USAGE: sendEmail.py -r "username@domain.com" -t "Title" -m "Message"

-r, --recipient=
  indicates the recipient of the email (to)
-t, --title=
  indicates the title or subject of the email
-m, --msg=
  indicates the body of text of the email'''
  
  print errorText
  log.warning(errorText)

def sendingMail(messageToSend):
  try:
    import json
    with open('..' + os.sep + 'extensions.json', 'r') as eFile:
      extensionInfo = json.load(eFile)

    # Import the email modules we'll need
    from email.mime.text import MIMEText
    msg = MIMEText(messageToSend["msg"])

    msg['Subject'] = messageToSend["subject"]
    msg['From'] = extensionInfo["palloo"]["email"]["address"]
    msg['To'] = messageToSend["recipient"]

    # Send the message via our Gmail server, but don't include the
    # envelope header.
    s = smtplib.SMTP('smtp.gmail.com', 587)
    s.starttls()
    s.login(msg['From'], extensionInfo["palloo"]["email"]["key"])
    s.sendmail(msg['From'], msg['To'], msg.as_string())
    s.quit()
    log.info("Email sent.")
  except smtplib.SMTPRecipientsRefused, e:
    log.error(e)
    sys.exit(1)
  except:
    log.error("Unexpected error: ")
    log.error(sys.exc_info()[0])
    log.error(sys.exc_info()[1])
    sys.exit(1)
  
def main(argv):
  msgSetup = {
    "recipient": "example@example.com",
    "subject": "No Subject",
    "msg": "Hello World"
  }
  log.info("Starting to parse arguments")
  try:
    opts, args = getopt.getopt(argv,"hr:m:t:", ["help", "recipient=", "msg=", "title="])
    if not opts:
		# Return proper usage of script if in error
		errPrint("No arguments provided. Printing help message and closing.")
		exit()
  except getopt.GetoptError:
    # Return proper usage of script if in error
    errPrint("No arguments provided. Printing help message and closing.")
    sys.exit(2)
  for opt, arg in opts:
    if opt in ("-h", "--help"):
      # Return proper usage of script if in error
      errPrint("Help message requested. Printing help message and closing.")
      sys.exit(2)
    elif opt in ("-r", "--recipient"):
      msgSetup["recipient"] = arg
    elif opt in ("-m", "--msg"):
      msgSetup["msg"] = arg
    elif opt in ("-t", "--title"):
      msgSetup["subject"] = arg

  log.info(msgSetup)
  log.info("Attempting to send email.")
  sendingMail(msgSetup)
  
if __name__ == '__main__':
    main(sys.argv[1:])