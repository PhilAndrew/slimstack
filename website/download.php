<?php
include("header.php");
?>
<div class="span-24 last">
<br />
<p><a href="index.php">Home</a> >> <b>Download SLiM Stack for Windows</b></p>
<p>
Note: That SLiM Stack is the combination of MongoDB, RockMongo, SBT (Simple build tool), Scala and Liftweb. This same setup can also be created without SLiM Stack and this is only a
  easy and convenient method, much like WAMP is a easy and convenient method to use PHP, MySql and Apache.
</p>
<p>
After SLiM Stack is installed, it is also added to your startup shortcuts folder, it will then start-up whenever your computer starts up and your Liftweb powered website will be available on port 8080, http://localhost:8080/
</p>
<p>When SLiM Stack starts up it will add a small icon on the Windows taskbar which says SLIM, as shown: <br /><img src="icons.png" /><br />
Clicking on the icon will show the SLiM Stack window and allow you to control the different programs.</p>
<p>
<img src="reloading.png" align="right" />When you change a file within the project, either a Scala file or html file, a small reloading progress bar will appear on the top right of the screen. The progress bar will progress until SLiM Stack has
    reloaded and refreshed the project. Your changes will them be reflected in the webpage if you reload the webpage just as if you changed a PHP file.
</p>
    <p>
    Before installing SLiM Stack, you must have Java 1.6 installed on your PC. If you are a Java programmer, it is most likely that you already have the Java 1.6 JDK installed correctly.
</p>
<p>
If you do not already have Java installed, you can get the Java JRE which is the simplest way to allow Java programs to run at <a href="http://www.java.com/en/download/">http://www.java.com/en/download/</a>.
 Alternatively, if you wish to also be able to program in Java get the Java JDK (Java Development Kit) at <a href="http://www.oracle.com/technetwork/java/javase/downloads/index.html">http://www.oracle.com/technetwork/java/javase/downloads/index.html</a>.
 If you intend to program in the Liftweb framework then I suggest retrieving the Java SDK.
</p>
<p>
 Java is the only prerequisite for SLiM Stack. Scala is not required, but obtaining and installing the Scala SDK is also very useful, especially if you wish to get <a href="ide.php">started with an IDE</a> to program your Liftweb program.
</p>

<p>
Download at <a href="http://code.google.com/p/slim-stack/downloads/list">Google hosted projects</a>.
</p>
</div>
<?php
include("footer.php");
?>
