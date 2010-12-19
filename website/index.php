<?php
include("header.php");
?>
        <div class="span-15">
        <br />
<p style="text-align:right">
<img src="welcome.png" style="padding-top:10px"  align="left"/>
    <a class="FlattrButton" style="display:none;"
    href="http://www.getslimstack.net/"></a>

</p>
<p>

    <br />&nbsp;<br />&nbsp;<br />
    <span class="st_twitter_hcount" displayText="Tweet"></span><span class="st_facebook_hcount" displayText="Share"></span><span class="st_email_hcount" displayText="Email"></span><span class="st_sharethis_hcount" displayText="Share"></span>

    &nbsp;<br />
</p>

         <p>Welcome to the <b>SLiM Stack</b>, SLiM stands for Scala + Lift + MongoDB.</p>
<p><a href="download.php">To download, click here.</a></p>

<p>SLiM is the fastest and easiest way to get started on Windows with these technologies. If you have used LAMP, WAMP or MAMP before then this is similar, but using
    a different set of technology.
SLiM's goal is to make it easy for beginners to get started with the Liftweb framework and to see how easy it is to create a website using this combination of technology.
         </p>
<p>SLiM allows you to change a file and see the results in the web browser in approximately 1 to 10 seconds later depending on the speed of your computer and file changed, so the cycle of changing code and seeing the results in the web
    browser is quick. This allows for fast development and testing of the changes made just as you would with PHP. Change code -> see the result -> change code again.
<a href="jrebel.php">Adding JRebel</a> can speed up this process.</p>
<!--<p>Do you want to see it working? Here is a sample video of developing in SLiM Stack.</p>
<p>
<object width="500" height="400"><param name="movie" value="http://www.youtube.com/v/k-T7vGdH_ek?fs=1&amp;hl=en_US&amp;rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/k-T7vGdH_ek?fs=1&amp;hl=en_US&amp;rel=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="500" height="400"></embed></object>
</p>-->
         <p><a href="http://www.scala-lang.org/">Scala</a> is an innovative computer language which works on the Java virtual machine and has full access to existing Java code. <a href="http://liftweb.net/">Liftweb</a> is a powerful web framework for programming
         websites using the Scala language, if you are a Java programmer it is not difficult to transition to Scala. <a href="http://www.mongodb.org/">MongoDB</a> is a cloud-like scalable database. This combination of technology is a robust solution for website development and can power large websites, for example
         <a href="http://foursquare.com/">Foursquare</a> uses Scala + Lift + MongoDB to handle a large number of customers. It is also in use at many other companies <a href="http://www.mongodb.org/display/DOCS/Production+Deployments">listed here</a>.</p>
         <p>Note: SLiM Stack is Alpha (first release) as of 16th Dec 2010 so may contain bugs, as a open source project, <b>please</b> report those bugs <a href="http://code.google.com/p/slim-stack/issues/list">here in the google bug issue list</a> or in the <a href="http://groups.google.com/group/slim-stack">google discussion group</a> and/or fix them yourself as
             it saves other people from the same troubles.</p>
         <p>

             <a href="download.php">Download SLiM Stack for Windows</a><br />
             <a href="#introduction">Introduction</a><br />
             <!--
             <a href="youtube.php">Youtube Videos</a><br />
             <a href="faq.php">F.A.Q. (Frequently Asked Questions)</a><br />
             <a href="documentation.php">Documentation</a><br /> -->
             <a href="learning.php">Books and Learning materials</a><br />
             <!--<a href="ide.php">Using an IDE with Liftweb</a><br />-->
             <a href="http://code.google.com/p/slim-stack/">Google project page</a><br />
             <a href="http://groups.google.com/group/slim-stack">Google discussion group</a><br />
             <a href="#">GITHUB Code</a><br />
             <a href="#copyright">Attribution/Copyright</a><br />
             <!--<br />
             <a href="future.php">Future directions and thoughts</a> >>--><br />
         </p>
       </div>
        <div class="span-9 last">
            <img src="slimstack.jpg" style="padding-top:10px" />
            <p>
                <b class="heading">SLiM Cooking Recipes</b>
            </p>
            <p>
                Example 1: <a href="example1.php">Adding and display pets</a>
            </p>
            <p>
                <b class="heading">Recent News</b>
            </p>
            <p>
                16th December 2010: SLiM Stack is made available in Alpha release
            </p>
        </div>
       <div class="span-24 last">
           <p>
               <a name="introduction"> </a>
               <b>Introduction</b></p>
           <p> <img src="slimshot1.jpg" align="right" style="padding-right:30px;padding-left:20px" />
               SLiM Stack is a windows program which installs on the windows taskbar just as WAMP does. It allows full control of Liftweb, MongoDB and administration of the MongoDB database
               through use of the PHP RockMongo program.
           </p>
           <p>After installing SLiM Stack it will automatically startup whenever your computer starts up by adding a shortcut to your windows startup folder.
           You can browse to the demo website by clicking on the top browser button which will send your browser to http://localhost:8080/.
           Then you can navigate to the pet example where it is possible to add and remove pets from a table on the page.
           </p>
           <p>
               If you wish to administrate the MongoDB database, click on the browser for RockMongo which will bring the user to the RockMongo screen which is
               similar to PhpMyAdmin for MySql. RockMongo allows you to admin the contents of the MongoDB database, if you wish to see more about <a href="http://code.google.com/p/rock-php/wiki/rock_mongo">RockMongo click here</a>.
           </p>
           <p>
           If you have questions about Liftweb, the best place to go is the <a href="http://groups.google.com/group/liftweb">Liftweb google discussion group</a>.
           There is a demonstration of a lot of Liftwebs functionality at <a href="http://demo.liftweb.net/">http://demo.liftweb.net/</a>.
           </p>

           <p>
               <a name="copyright"> </a>
               <b>Attribution/Copyright</b></p>
           <p>The idea of SLiM Stack originates with <a href="http://programminggeek.com/">Brian Knapp</a>.</p>
           <p>SLiM Stack is implemented and programmed by <a href="http://www.philipandrew.com/">Philip Andrew</a> of <a href="http://www.orsa-studio.com/">Orsa Studio</a> Hong Kong, copyright of SLiM Stack code is retained by Orsa Studio and released under the Apache 2 open source licence.</p>
           <p>This software contains many other libraries and software which include:<br />
           <a href="http://liftweb.net/">Liftweb</a> (Apache 2 licence)<br />
           <a href="http://www.scala-lang.org/">Scala</a> (The <a href="http://www.scala-lang.org/node/146">Scala licence</a>)<br />
           <a href="http://www.mongodb.org/">MongoDB</a> (<a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU AGPL</a> see <a href="http://www.mongodb.org/display/DOCS/Licensing">MongoDB page</a>) note that
               Mongo DB drivers are all licensed under an Apache license. Your application, even though it talks to the database, is a separate program and “work” so
               it is not affected by the AGPL.<br />
           Many jar files which I will not list as they will most likely change over time.<br />
           The image of books on the top right is purchased from IStockPhoto File #: 1971134, Standard License<br />
           </p>

       </div>
<?php
include("footer.php");
?>