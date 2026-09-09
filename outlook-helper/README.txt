ATS "Email PI" — desktop Outlook auto-attach helper
====================================================

WHAT IT DOES
------------
Adds an "Email PI (Outlook)" button in the app. Clicking it:
  1. Renders the exact PI you are viewing to a PDF using YOUR Chrome/Edge
     (headless, so it looks just like the printed PI).
  2. Opens a NEW Outlook desktop email with Subject + body prefilled and the
     PI PDF already attached. You just add the recipient and hit Send.

Everything runs on the user's own PC — no server-side PDF is required.

REQUIREMENTS (per PC)
---------------------
  - Google Chrome (or Microsoft Edge) installed.
  - Microsoft Outlook (desktop) installed and set up.
  - The user is logged into the ATS app in Chrome (so the PI page renders).

ONE-TIME INSTALL (do this on each PC that needs the button)
-----------------------------------------------------------
1. Create a folder, e.g.  C:\ats-outlook-helper
2. Copy these two files into it:
       AtsMailHelper.ps1
       ats-mail-launch.cmd
3. Open  register-atsmail.reg  in Notepad and confirm the path matches where you
   put the files (default is C:\ats-outlook-helper). Save.
4. Double-click  register-atsmail.reg  and accept (registers the atsmail:// link
   for the current Windows user — no admin rights needed).
5. Done. Test by clicking the "Email PI (Outlook)" button in the app.

FIRST CLICK
-----------
The browser will ask "Open ats-mail-launch.cmd?" — tick "Always allow" so it
won't ask again on that PC.

TROUBLESHOOTING
---------------
  - "Chrome or Edge was not found": install Chrome, or edit the candidate paths
    in AtsMailHelper.ps1.
  - Nothing happens: make sure register-atsmail.reg was applied and its path
    points to ats-mail-launch.cmd.
  - PDF is blank / login page: make sure you are logged into ATS in Chrome first.
  - Outlook doesn't open: make sure the desktop Outlook app is installed (this
    uses Outlook COM automation, not webmail).

UNINSTALL
---------
Delete the folder and remove the key:
  HKEY_CURRENT_USER\Software\Classes\atsmail
