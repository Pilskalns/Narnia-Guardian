# Narnia Guardian 2.0 - server backdoor killer
#### N!B! Read everything before begin clean up
### Level - intermediate - there is manual work involved, you should have some level of confidence of your written code and actions, as well as basic understanding of server enviroment

Maybe you are here because of [MailPoet](http://blog.sucuri.net/2014/10/wordpress-websites-continue-to-get-hacked-via-mailpoet-plugin-vulnerability.html)  or [StackOverflow](http://stackoverflow.com/questions/25996752/removing-a-string-in-a-php-file-with-start-and-end/28430880)

This tool is created to clean infected PHP files which contains obfuscated code or contains dangerous server backdoor. If you got this bad code on your server, it could be triggered any time and could do anything on your server. In fact, purpose and content of this malware code also could be changed anytime. Code could be stealing passwords, sending spam e-mail from your IP or even hosting illegal copy of Torrent files and steal traffic you pay for. Once your IP is globally blacklisted, it is hard to get back SEO on Google etc.

**This tool is only Helper to fix already broken things. You shouldn't rely on this as primary protection. Correctly set server environment is first thing to check after attack. I can not teach you complex algorithms, but I can give you a sample of what I have taught**

---

## Where does malware code reside?
It will be in begining of PHP file and begins and closes with `<?php` and `?>`. This is safest way to inject this code inside already existing PHP file. In future, malware could get smarter and hide between set of valid code. So, for example if **you have**

``` PHP
<?php
/* Here comes my super-duper code */
 
?> // May or may not contain ending tag
```
Or even plain HTML inside .php file

``` HTML
<!DOCTYPE html>
<html>
	<title>Narnia Guardian</title>

etc.
```

**After malware injection file will look something like**
``` PHP
<?php if(!isset($GLOBALS["\x61\156\x75\156\x61"])) { $ua=strtolower($_SERVER["\x48\124\x54\120\x5f\125\x //etc. ending with ?> 
<?php
/* Here comes my super-duper-legit code */
````

Even hackers have to respect correct syntax of code... So it is easy to spot it by eye if you have turned on your editor word wrap, because in many cases bad code is prefixed with ton of spaces to hide it in code editor. Not like Python, additional spaces in PHP does not affect code execution...

## Patterns
You might say, `but wait, there must be way to see patterns and predict which is good or bad code!`. Yes there is, but then you have to dig deep into how this bad code is structured. I have given already my patterns, but you can easily add your in `index.php`, or ocen create complicated functions which tells if given code sample is good or not. Up to you my friend, Narnia 2.0 is about opening up easy adjusting to custom solutions.

## How does Narnia Guardian works?
It now becomes clear, why I told you all that - 

1. NG will search for PHP files and split those files up by matching portions of php code that looks like `<?php ... ?>`.
2. So, every matching sample get's checked against regex patterns I have developed or library samples in `blacklist.dat`
3. If something matches - everything inside those matching pairs of PHP tags will be removed, including tags itself, to maintain clean code

N!B! For every attack case - malware code is different - **You have to update regex libraries and blacklist until you are confident**

---
## Order of actions
0. Create test copy of infected code + backup zip if anything goes wrong, so you can revert things (strongly suggested)
1. Download / Upload script to test location 
2. Modify `index.php` to match your case
3. Run script by browsing location on browser
4. Inspect output of script - there will be block's of obfuscated code - right before it, there should be outputted location where it comes from
5. Inspect source of obfucated block file - if it is clear that this is not your code or other good minified code, search for string that could be as key string to recognize it, as example `if(!isset($GLOBALS["\x61\156\x75\156\x61"]))` or meaningless variables `$bmhqhhzolg` or `$pjro=22;$vnlpv=$pjro+42;` - copy these kind of strings to `blacklist.dat` library - one sample per one line
1. Repeat steps 3 to 5. If output is much more shorter, it means it is working, don't stop until you are sure that your all of your files are clean. When you are confident, that test is not ruining good code, put it against original code, but anyways, keep backup of it.

## What You should do next?
* Do clean install as much as you can - Install clean wordpress, install clean theme etc. 
* Copy this script to safe place, chmod it for safety. If in bad hands - it could do bad things out of the box.
* Change ALL passwords, I mean ALL - WordPress, databases, WordPress salt, user passwords, secret keys, everything - all paswords could be readed by malware code
* Update your OSS or paid software for latest versions, including WordPress, plugins, extensions, anything you have
* **chmod** correct file permissions for your project. It could be `755` for directory, `644` for files. (**please commit here!**)
* If it is your own code - walk OVER it ALL manually - check if it is escaped from form inputs, SQL injections etc.
* Ask your hosting provider to assign new public IP
* Pray God, that your super-secret files didn't got stolen
* google more about this issue

---

## Why does `Narnia`?
Project names containing `Narnia` I give if the code is meant to be run into private / hidden locations, without public access. If you see my project containing `Narnia` and you don't have any idea why you see it, it means that you have run into wrong place or something is broken and now you see it. Just like in the movie, it is a real magic...

---
# More N!B! - test it first - You could lose content of all your php files or ruin code inside them.
**I strongly suggest to test clean up script on localhost with corrupted files or at least on copy inside your host. Once I release this to public domain, script runs perfectly, but hackers don't sleep and they could affect their code, so my guardian won't clean it up any more or so that script will delete more than should.**
