Read everything before begin clean up
# Narnia Guardian
This tool is created to clean infected PHP files with obfuscated code. Donated whole workday to clean up private server, please contribute with code comments and other suggestions.

|File					| Role
|-----------------------|---------------------------
|NarniaGuardian.php		| Contains cleaner class
|blacklist.txt			| Here insert library of malware samples
|uniquelist.txt			| List of unique first lines of php files


## Order of actions
1. Download / Upload script to test location (strongly suggested)
2. Modify / copy index.php content between first section of <?php ... ?>
3. Run script by browsing location on browser
4. Open uniquelist.txt, search for malware - copy typical sample of code to blaclklist.txt library
5. Check logs folder for success. The one named root-error[..].log will contain list of files, which are suspicious, but could be some large class file. These should be deleted manually.
1. Repeat 3. to 6. 

# N!B! Test it first - You could lose all your files
### I strongly suggest to test clean up script on localhost with corrupted files or at least on copy inside your host. Once I release this to public domain, script runs perfectly, but hackers don't sleep and they could affect their code, so my guardian won't clean it up anymore or affect it so my script will delete more than should.
