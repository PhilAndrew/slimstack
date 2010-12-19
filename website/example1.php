<?php
include("header.php");

// Note to self, span class="hidden" contains stuff which could be shown
?>
<div class="span-23 last">
<br />
    <p><a href="index.php">Home</a> >> <b>Example 1: Adding and displaying pets</b></p>
<p>
<p>
Note there are many other examples of Liftweb, there is also a demonstration of Liftweb functionality at <a href="http://demo.liftweb.net/">http://demo.liftweb.net/</a> if you wish
    to see more demos of Liftweb.
     This is a very simple example to show a common use case of adding a form inputs to the database and showing them in a table. The code for this
    example is relatively short and comes with the SLiM Stack installation.<br />
  This example is aimed at the complete beginner and minimal Scala specific terms are used.
</p>
<p><b>Table of contents</b>
</p>
<p>
    <a href="#chap1">1. What does this example do?</a><br />
    <a href="#chap2">2. Files used in this example</a><br />
    <a href="#chap3">3. Adding the database class</a><br />
    <a href="#chap4">4. A form to submit a pet to the database</a><br />
    <a href="#chap5">5. PetSnippet class used to bind and store pet form data</a><br />
    <a href="#chap6">6. Displaying the pets in a table</a><br />
    <a href="#chap7">7. Edit and delete of pets</a><br />
    <a href="#chap8">8. Conclusion</a><br />
    <a href="#chap9">9. PetSnippet.scala</a><br />
    <a href="#chap10">10. pet.html</a><br />
    <a href="#chap11">11. edit.html</a><br />
    <a href="#chap12">12. Pet.scala (database record)</a><br />
</p>
<p>
    <b><a name="chap1" href="#"> </a>1. What does this example do?</b>
</p>
<p>
This example allows you to add pets to the MongoDB database through a html page where a pet has a name, age and description. Also you will be able to
    see all the pets in a paginated table and edit any pet or delete any pet. It uses Liftweb Framework to do all of this.<br />&nbsp;<br />
After starting SLiM Stack, http://localhost:8080/pet and you will see:<br />
<img src="example1localhostpet.png" />
</p>
<p>
After adding a pet in the form, the table will show the added pet, from here you can enter another pet, delete this pet in the table or click edit to edit the pet in the table.<br />
<img src="example1localhostpet2.png" />
</p>

<p><b><a name="chap2" href="#"> </a>2. Files used in this example</b></p>
<p>
This tutorial is a Liftweb web application which allows for the adding of pets, display of those pets in a HTML table,
  editing and deleting those pets. The code for this comes with SLiM Stack when you install it and can be found
  in the project files. After installing SLiM Stack click on the button <b>Open Project Files: Open Folder</b>.
</p>
<p>
<img src="example1slimstack.png" /><br />
<img src="example1shot1.png" />
</p>
<p>
The folders shown here are <b>scala</b> which contains the scala classes for the application, <b>webapp</b> contains
 the html files and templates. For this example the relevant files for this application are contained in the following locations.<br />
<table>
    <thead>
        <tr><td>File or folder</td><td>Description</td></tr>
    </thead>
    <tr><td>main\webapp\pet.html</td><td>Shows a table with pagination of all pets in the MongoDB database. Also allows for addition of more pets through use of a form.<br />
    This file is accessed through the URL http://localhost:8080/pet</td></tr>
    <tr><td>main\webapp\pet\edit.html</td><td>Shows a form for the editing of a single pet.<br />This file is accessed through the URL http://localhost:8080/pet/edit</td></tr>
    <tr><td>main\scala\code\snippet\PetSnippet.scala</td><td>PetSnippet provides the logic for adding, deleting and displaying of pets from the database, this class is bound to the html
        page in the files pet.html and edit.html.</td></tr>
    <tr><td>main\scala\bootstrap\liftweb\Boot.scala</td><td>When liftweb starts up, this Scala class is used to set up initialization information regarding the application.<br />
    To allow the html files to be accessed from the browser, they must be placed within the sitemap inside this Boot.scala file.<br />
        <pre class="brush: scala;">
  def sitemap() = SiteMap(
    Menu("Home") / "index",
    Menu("Pet") / "pet" submenus(
      Menu("Edit Pet") / "pet" / "edit")
      )
            </pre>
    </td></tr>

    <tr><td>main\webapp\templates-hidden\default.html</td><td>This is the surrounding template which contains the header, footer and menu and is used for each page in this example.</td></tr>
    <tr><td>main\scala\code\model\Pet.scala</td><td>This is the MongoDB database class which defines the columns of the pet and how it can be stored.</td></tr>
    <tr><td>main\scala\code\db\SlimstackDemoDB.scala</td><td>This sets the database name.<br />
        <pre class="brush: scala;">
  	def setup {
	    MongoDB.defineDb(DefaultMongoIdentifier,
        MongoAddress(MongoHost(), "slimstack_demo"))
	}
            </pre>
    </td></tr>
</table>
</p>
<p><b><a name="chap3" href="#"> </a>3. Adding the database class</b></p>
<p>
The first step is to tell Liftweb that we want to store a class into the database, this is done by adding the Pet.scala class in \scala\code\model\Pet.scala.
</p>
<p>
    <pre class="brush: scala;">
    package code.model

    import scala.List
    import net.liftweb.util.FieldError
    import net.liftweb.record.field._
    import net.liftweb.common._
    import net.liftweb.json.JsonAST._
    import com.mongodb._
    import com.mongodb.util.JSON
    import org.bson.types.ObjectId
    import net.liftweb.mongodb.record._
    import net.liftweb.mongodb.record.field._
    import net.liftweb.mongodb._
    import net.liftweb.json.JsonDSL._

    class Pet extends MongoRecord[Pet] with MongoId[Pet] {
      def meta = Pet

      // The pets name
      object name extends StringField(this, 20)

      // The pets age
      object age extends LongField(this)

      // A description of the pet
      object description extends StringField(this, 128) {

        // The validations is a overridden method
        override def validations = validLength _ :: super.validations

        def validLength(in: String): List[FieldError] = {
          if (in.size >= 3) Nil
          else List(FieldError(this, <b>Description must be at least 3 characters</b>))
        }

      }
    }

    object Pet extends Pet with MongoMetaRecord[Pet] {
    }
        </pre>
</p>
<p>
This Pet.scala class tells Liftweb that the Pet class will be stored to the database as a MongoRecord.
<pre class="brush: scala;">
    class Pet extends MongoRecord[Pet] with MongoId[Pet] {
</pre>
</p>
<p>
MongoRecord tells Liftweb that your class is using the record style to store data.
 Since every MongoDB record must have an id field, it is convenient to use MongoId to bring in a way to automatically
 add unique ids to the Pet. This is like a auto-generated id field in a normal database.
 Otherwise if you did not use MongoId you would need to generate your own ids by overriding the method called id on the
 superclass MongoRecord.
</p>
<p>
A method called <b>meta</b>, known in Scala as a function, is used to allow Liftweb to determine the object
    which contains meta-data. This is necessary to tell Liftweb what object stores
    meta-data about the Pet.
<pre class="brush: scala;">
def meta = Pet
</pre>
</p>
<p>
The fields to be stored for this pet are object instances of fields. There are many fields available, in this example
only StringField and LongField is used. Some other fields which could be used are:<br />
    </p>
<p>
BinaryField, BooleanField, CountryField, DateTimeField, DecimalField, DoubleField, EmailField, EnumField, 
 EnumNameField, IntField, LocaleField, LongField, NumericField, PasswordField, PostalCodeField,   
 StringField, TextareaField, TimeZoneField.
</p>
<p>Some MongoDB specific fields are:</p>
<p>
DBRefField, DateField, JObjectField, JsonObjectField, MongoCaseClassField, MongoFieldFlavor, MongoListField, MongoMapField, MongoPasswordField, ObjectIdField, PatternField, UUIDField.
</p>
<p><b><a name="chap4" href="#"> </a>4. A form to submit a pet to the database</b></p>
<p>Now that we have a way to store a pet to the database, we need to actually do the work to allow a user to submit a form and to place its contents into the database.
 In the file pet.html there is the following code.
<pre class="brush: xml;">
    <div class="lift:surround?with=default;at=content">
        <div>
            ... (code hidden here) ...
            <div id="editForm" class="lift:PetSnippet.editForm?form=post">
                <label for="editName">Pet name :</label><input id="editName"/><br />
                <label for="editAge">Pet age :</label><input id="editAge"/><br />
                <label for="editDescription">Pet description :</label><input id="editDescription"/><br />
                <input type="submit" value="submit"/>
            </div>
        </div>
    </div>
</pre>
</p>
<p>
What is going on here? Well this html file is firstly referencing the template which surrounds it. The <b>lift:surround?with=default;at=content</b> is saying that
  Liftweb should surround this &lt;div with the template called default, it is located in main\webapp\templates-hidden\default.html.
  It is placing the contents of this div into the html template called default.html and then returning the result, this allows this div to specify the template
  which it is a part of. Also note that this information is stored in the class
  part of the div. This allows the template to be developer friendly for web developers as the template itself is still relatively normal html and the web developer can
  edit this file independent of the code logic.
</p>
<p>
Then within this we have the div editForm.
<pre class="brush: xml;">
 <div id="editForm" class="lift:PetSnippet.editForm?form=post"> ... </div>
</pre>
</p>
<p>
The editForm has a class of <b>lift:PetSnippet.editForm?form=post</b> which instructs Lift to call the method editForm on the PetSnippet. Lift finds the class PetSnippet in the
    main\scala\code\snippet\ folder. How does Lift know where to find the snippet class? In the Boot.scala of our project we called a function
    <b>LiftRules.addToPackages("code")</b> which tells Lift where to find snippets, views, comet actors and lift ORM Model objects. So far we have only discussed on the ORM model and
    the snippet called PetSnippet.
</p>
<p>So far this form is defined in the HTML of the file pet.html and the form must be bound to a real object on the server side so that the server can read the data
  from the submitted form. For this it is necessary to introduce the PetSnippet.scala class.</p>
<p><b><a name="chap5" href="#"> </a>5. PetSnippet class used to bind and store pet form data</b></p>
<p>
The class PetSnippet is a snippet, snippets are a type of component which exists on the server side. In this case, the <b>PetSnippet extends StatefulSnippet</b> which means
    that the snippets state will persist from one page load to another. Even in the case that the snippet is used on a different page, the same state is stored
    for the instance of the snippet on that page. This means that a snippet can span multiple pages and still show the same information, for example
    a shopping cart needs to show the same state across many pages, so this would be a good match for a StatefulSnippet. Not all Snippets need to be stateful.
</p>
<p>Another way to think of a StatefulSnippet is that it is an object which contains state for a period of time, this is similar to a SEAM conversational context or
  a session variable which exists for a period of time and then disappears. In the PetSnippet, the state being held is the editingPet variable which holds
 the current pet either being created or edited. Since the PetSnippet will exist on two different pages pet.html and pet/edit.html, it is useful for it to be a stateful snippet.</p>
<p>
<pre class="brush: scala;">

package code.snippet

import _root_.net.liftweb.common._
import _root_.net.liftweb.util._
import _root_.net.liftweb.http._
import _root_.net.liftweb.mapper._
import _root_.net.liftweb.util.Helpers._
import _root_.net.liftweb.sitemap._
import _root_.scala.xml._
import _root_.net.liftweb.http.S._
import _root_.net.liftweb.http.RequestVar
import _root_.net.liftweb.util.Helpers._
import _root_.net.liftweb.common.Full
import code.model.Pet
import net.liftweb.mongodb.{Skip, Limit}
import _root_.net.liftweb.http.S._
import _root_.net.liftweb.mapper.view._
import com.mongodb._

class PetSnippet extends StatefulSnippet with PaginatorSnippet[Pet] {

  var dispatch: DispatchIt = {
    case "editForm" => editForm _
  }

  var editingPet = Pet.createRecord

  ... (code hidden here) ...

  def editForm(xhtml: NodeSeq): NodeSeq = {
        ("#editName" #> editingPet.name.toForm &
         "#editAge" #> editingPet.age.toForm &
         "#editDescription" #> editingPet.description.toForm &
          "type=submit" #> SHtml.submit(?("Save"), () => save )).apply(xhtml)
  }

  def save = {
    editingPet.save
    redirectToHome
  }

  ... (code hidden here) ...

  def redirectToHome = {
    editingPet = Pet.createRecord
    redirectTo("/pet")
  }

}

</pre>

</p>
<p>
The dispatch function exists for two reasons, firstly it would not be necessary to have it, the reason it is there is that for a stateful snippet
  we may want multiple individual instances of the snippet on the same page. The dispatch allows us to create
  multiple instances of the snippet by controlling the matching calls on the dispatch. Since we do not use multiple instances
    of this PetSnippet, it is not logically needed, but this is required for all stateful snippets due to this potential use.
  Since it is required for stateful snippets then we should match the dispatch calls to our functions in the PetSnippet, therefore editForm goes directly to editForm.
</p>
<p>
The line <b>case "editForm" => editForm _</b> matches the function call editForm directly to the function <b>def editForm</b>.
</p>
<p>
<pre class="brush: xml;">
<div id="editForm" class="lift:PetSnippet.editForm?form=post">
                <label for="editName">Pet name :</label><input id="editName"/><br />
                <label for="editAge">Pet age :</label><input id="editAge"/><br />
                <label for="editDescription">Pet description :</label><input id="editDescription"/><br />
                <input type="submit" value="submit"/>
</div>
</pre>
</p>
<p>
  So we know that the above HTML fragment is matched by Liftweb to the PetSnippet editForm function. The editForm function binds
  objects in the html to objects in Scala code. <b>"#editName" #> editingPet.name.toForm</b> binds the html element with id of #editForm
  to the scala object returned by <b>editingPet.name.toForm</b>, then editAge and editDescription are easy to understand.
</p>
<p>
The submit button here is interesting, <b>"type=submit" #> SHtml.submit(?("Save"), () => save )</b> binds the
    <b>&lt;input type="submit" value="submit"/&gt;</b> to the SHtml.submit function call which makes a button that when clicked will
    call the server side PetSnippet function in the second parameter of <b>SHtml.submit</b>. The key to understanding is that <b>() => save</b> is
    a function which calls the save function, since in Scala, functions can be created with this syntax. The function is passed
    as the second parameter of SHtml.submit. What this means is that when a user clicks on the submit button on the html form,
    then the server side function <b>def save = { ...</b> will be called.
</p>
<p>
There is an interesting thing going on here, if you view the HTML source of the page at http://localhost:8080/pet/ you can see the
 form for adding a pet has unusual names for the form inputs. Liftweb does the binding between the form elements names
    behind the scenes.
 This is one of the many features which make Liftweb more secure than
 most web framework, this particular feature prevents replay attacks. Liftweb is better at dealing with most of the <a href="http://www.owasp.org/index.php/Category:OWASP_Top_Ten_Project">OWASP Top 10</a> than any other web framework.
<pre class="brush: xml;">
<form action="/pet" method="post"><div>
    <input name="F567313851833ANP" type="hidden" value="true" /></div>
    <div id="editForm">
    <label for="editName">Pet name :</label>
        <input id="name_id_field"
      value="" tabindex="1" maxlength="20" type="text" name="F567313851829VZA" /><br />
    <label for="editAge">Pet age :</label>
        <input id="age_id_field"
        value="0" tabindex="1" type="text" name="F567313851830OUC" /><br />
    <label for="editDescription">Pet description :</label>
        <input id="description_id_field"
        value="" tabindex="1" maxlength="128" type="text" name="F567313851831MJ5" /><br />
    <input value="Save" type="submit" name="F567313861832KQZ" />
</div></form>
</pre>
</p>
<p>
When the user clicks on the form, the form submits to the server and the function passed to SHtml.submit as <b>() => save</b> will be called.
 Since the forms inputs are bound to the <b>editingPet</b> in the <b>PetSnippet</b>, the <b>editingPet</b> is populated with this forms values.
 The save function in PetSnippet instructs the pet to save to the MongoDB database and redirects the user to the home page for pets.
<pre class="brush: scala;">
def save = {
  editingPet.save
  redirectToHome
}

def redirectToHome = {
  editingPet = Pet.createRecord
  redirectTo("/pet")
}
</pre>
</p>
<p><b><a name="chap6" href="#"> </a>6. Displaying the pets in a table</b></p>
<p>
Since we have been storing pets in the MongoDB database, we need to get them out and display them to the user, this
  is usually done in the form of a table.
<pre class="brush: xml;">
<h3>Pets</h3>
<table>
    <thead>
        <tr>
            <th class="span-1">Edit</th>
            <th class="span-1">Delete</th>
            <th class="span-3">Pet name</th>
            <th class="span-3">Pet age</th>
        </tr>
    </thead>
    <tr class="lift:PetSnippet.showAll">
        <td class="petEdit">Edit link goes here</td>
        <td class="petDelete">Delete link goes here</td>
        <td class="petName">Pet name goes here</td>
        <td class="petAge">Pet age goes here</td>
    </tr>
</table>
</pre>
</p>
<p>
The <b>&lt;tr class="lift:PetSnippet.showAll"&gt;</b> calls the server side function showAll on the PetSnippet.
 showAll is a function which takes the XML contents <b>xhtml: NodeSeq</b> and returns XML. The XML contents are
 what is inside the &lt;tr tag.<br /> <b>page.flatMap</b> loops over all items returned by the <b>override def page</b> function and
 maps them to the XML.
<p>
<p>As can be seen, the contents ".petEdit *" of the html element with class of .petEdit are replaced with a link which redirects the user to a new page at URL of pet/edit and also
 calls a function called edit passing the pet to be edited. The ".petDelete *" replaces the contents of &lt;td class="petDelete"&gt; with a link to delete,
 passing the pet to be deleted. The ".petName" contents is replaced with the pet name attribute and the same for age. This is a joining, a mapping between the data
 returned by the page function on the server side and the HTML for the rows of the table.</p>

<p>A real advantage here is that each pet returned by the function page is joined with the HTML in such a way that we
 do not need to pass object identifiers. If this was programmed in PHP then normally we would output the object ID into the table for each link so that when
 someone clicks on the link we could tell what database record they are clicking on. Here, the binding between the server side and client
 side is conveniently managed by Liftweb. Also for edit and delete, the binding between the html element and the server side function to be called is declared all
 within one simple function, <b>showAll</b>.
<pre class="brush: scala;">
  def showAll(xhtml: NodeSeq): NodeSeq = {
    page.flatMap(pet => {
      ( ".petEdit *" #> link("pet/edit", () => edit(pet), Text("Edit")) &
        ".petDelete *" #> link("", () => delete(pet), Text("Delete")) &
        ".petName *" #> pet.name &
        ".petAge *" #> pet.age).apply(xhtml)
    })
  }
</pre>
</p>
<p>
 This function uses the findAll function on the Mongo Record by creating a default query, limiting the number
 of results to be equal to itemsPerPage, skip the first (curPage*itemsPerPage) items. This gives the ability
 to query a page of items from the database.
<pre class="brush: scala;">
override def page = Pet.findAll(QueryBuilder.start().get(), Limit(itemsPerPage), Skip(curPage*itemsPerPage))
</pre>
</p>
<p>
There are three functions in PetSnippet which allow the pagination to work and they are overidden functions
    from the PaginatorSnippet trait (like a class).
<pre class="brush: scala;">
override def count = Pet.count
override def itemsPerPage = 5
override def page = Pet.findAll(QueryBuilder.start().get(), Limit(itemsPerPage), Skip(curPage*itemsPerPage))
</pre>
</p>
<p>
Since PetSnippet implements the trait PaginatorSnippet, then this snippet can do pagination. To show the pagination
    control bar in html we need to call the paginate function in the template. The paginate function is implemented in
    PaginatorSnippet, <b>&lt;div class="lift:PetSnippet.paginate"&gt;</b> calls this function.
<pre class="brush: xml;">
<div class="lift:PetSnippet.paginate">
    &lt;nav:first /&gt; | &lt;nav:prev /&gt; | &lt;nav:allpages /&gt; |  &lt;nav:next /&gt; | &lt;nav:last /&gt; | &lt;nav:records /&gt;
</div>
</pre>
</p>

<p><b><a name="chap7" href="#"> </a>7. Edit and delete of pets</b></p>
<p>
The showAll function inserts edit and delete links for each pet into the table. The edit link is
    <b>".petEdit *" #> link("pet/edit", () => edit(pet), Text("Edit"))</b> which does two things, it takes the user to the pet/edit.html page
    and calls the edit function on this PetSnippet. The edit function assigns the current pet being edited from the table. When the user submits the edit page
    then the pet is saved again into the database.
</p>
<p>
The delete function works in a similar way, <b>".petDelete *" #> link("", () => delete(pet), Text("Delete"))</b> inserts the delete
 link into the table, when the user clicks this link, then the pet is deleted as the function <b>() => delete(pet)</b> is called.
</p>


<p><b><a name="chap8" href="#"> </a>8. Conclusion</b></p>
<p>
Liftweb provides the ability to produce concise and declarative code which is easy to understand.
    It also abstracts away the need to write code to work out the binding between database, server side code
    and client side code. As was shown in this example, in the table showing pets, the pet objects can be referred
    directly in the server side code and when a user clicks on edit or delete, the same pet which is referred to
    on the server side is
    automatically bound all the way from the database, through the server to the client side and back again. This binding
    is a powerful feature which reduces the total amount of code needed.
</p>
<p>

</p>
<p><b><a name="chap9" href="#"> </a>9. PetSnippet.scala</b></p>
<p>
<pre class="brush: scala;">
package code.snippet

import _root_.net.liftweb.common._
import _root_.net.liftweb.util._
import _root_.net.liftweb.http._
import _root_.net.liftweb.mapper._
import _root_.net.liftweb.util.Helpers._
import _root_.net.liftweb.sitemap._
import _root_.scala.xml._
import _root_.net.liftweb.http.S._
import _root_.net.liftweb.http.RequestVar
import _root_.net.liftweb.util.Helpers._
import _root_.net.liftweb.common.Full
import code.model.Pet
import net.liftweb.mongodb.{Skip, Limit}
import _root_.net.liftweb.http.S._
import _root_.net.liftweb.mapper.view._
import com.mongodb._

class PetSnippet extends StatefulSnippet with PaginatorSnippet[Pet] {

  var dispatch: DispatchIt = {
    case "showAll" => showAll _
    case "editForm" => editForm _
    case "paginate" => paginate _
  }

  var editingPet = Pet.createRecord

  def showAll(xhtml: NodeSeq): NodeSeq = {
    page.flatMap(pet => {
      ( ".petEdit *" #> link("pet/edit", () => edit(pet), Text("Edit")) &
        ".petDelete *" #> link("", () => delete(pet), Text("Delete")) &
        ".petName *" #> pet.name &
        ".petAge *" #> pet.age).apply(xhtml)
    })
  }

  def editForm(xhtml: NodeSeq): NodeSeq = {
        ("#editName" #> editingPet.name.toForm &
         "#editAge" #> editingPet.age.toForm &
         "#editDescription" #> editingPet.description.toForm &
          "type=submit" #> SHtml.submit(?("Save"), () => save )).apply(xhtml)
  }

  override def count = Pet.count
  override def itemsPerPage = 5
	override def page = Pet.findAll(QueryBuilder.start().get(), Limit(itemsPerPage), Skip(curPage*itemsPerPage))

  def edit(pet : Pet ) = {
    editingPet = pet
  }

  def delete(pet : Pet ) = {
    pet.delete_!
    redirectToHome
  }

  def save = {
    editingPet.save
    redirectToHome
  }

  def redirectToHome = {
    editingPet = Pet.createRecord
    redirectTo("/pet")
  }

}
</pre>
</p>
<p><b><a name="chap10" href="#"> </a>10. pet.html</b></p>
<p>
<pre class="brush: xml;">
    <div class="lift:surround?with=default;at=content">
        <div>
            <h3>Pets</h3>
            <table>
                <thead>
                    <tr>
                        <th class="span-1">Edit</th>
                        <th class="span-1">Delete</th>
                        <th class="span-3">Pet name</th>
                        <th class="span-3">Pet age</th>
                    </tr>
                </thead>
                <tr class="lift:PetSnippet.showAll">
                    <td class="petEdit">Edit link goes here</td>
                    <td class="petDelete">Delete link goes here</td>
                    <td class="petName">Pet name goes here</td>
                    <td class="petAge">Pet age goes here</td>
                </tr>
            </table>
            <div class="lift:PetSnippet.paginate" xmlns:nav="navigate">
                &lt;nav:first /&gt; | &lt;nav:prev /&gt; | &lt;nav:allpages /&gt; |  &lt;nav:next /&gt; | &lt;nav:last /&gt; | &lt;nav:records /&gt;
            </div>
            <br />
            <h3>Add pet:</h3>
            <div id="editForm" class="lift:PetSnippet.editForm?form=post">
                <label for="editName">Pet name :</label><input id="editName"/><br />
                <label for="editAge">Pet age :</label><input id="editAge"/><br />
                <label for="editDescription">Pet description :</label><input id="editDescription"/><br />
                <input type="submit" value="submit"/>
            </div>
        </div>
    </div>

</pre>
</p>
<p><b><a name="chap11" href="#"> </a>11. edit.html</b></p>
<p>
<pre class="brush: xml;">
    <div class="lift:surround?with=default;at=content">
        <span>Edit pet:</span><br />
        <div id="editForm" class="lift:PetSnippet.editForm?form=post">
            <label for="editName">Pet name :</label><input id="editName"/><br />
            <label for="editAge">Pet age :</label><input id="editAge"/><br />
            <label for="editDescription">Pet description :</label><input id="editDescription"/><br />
            <input type="submit" value="submit"/>
        </div>
    </div>
</pre>
</p>

<p><b><a name="chap12" href="#"> </a>12. Pet.scala (database record)</b></p>
<p>
<pre class="brush: scala;">
    package code.model

    import scala.List
    import net.liftweb.util.FieldError
    import net.liftweb.record.field._
    import net.liftweb.common._
    import net.liftweb.json.JsonAST._
    import com.mongodb._
    import com.mongodb.util.JSON
    import org.bson.types.ObjectId
    import net.liftweb.mongodb.record._
    import net.liftweb.mongodb.record.field._
    import net.liftweb.mongodb._
    import net.liftweb.json.JsonDSL._

    class Pet extends MongoRecord[Pet] with MongoId[Pet] {
      def meta = Pet

      // The pets name
      object name extends StringField(this, 20)

      // The pets age
      object age extends LongField(this)

      // A description of the pet
      object description extends StringField(this, 128) {

        // The validations is a overridden method
        override def validations = validLength _ :: super.validations

        def validLength(in: String): List[FieldError] = {
          if (in.size >= 3) Nil
          else List(FieldError(this, <b>Description must be at least 3 characters</b>))
        }

      }
    }

    object Pet extends Pet with MongoMetaRecord[Pet] {
    }

</pre>
</p>




<p>
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
    <br />&nbsp;
</p>


</div>
<?php
include("footer.php");
?>
