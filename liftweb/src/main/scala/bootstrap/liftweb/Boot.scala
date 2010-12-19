package bootstrap.liftweb

import _root_.net.liftweb.util._
import _root_.net.liftweb.common._
import _root_.net.liftweb.http._
import _root_.net.liftweb.http.provider._
import _root_.net.liftweb.sitemap._
import _root_.net.liftweb.sitemap.Loc._
import Helpers._
import _root_.net.liftweb.mapper.{DB, ConnectionManager, Schemifier, DefaultConnectionIdentifier, StandardDBVendor}
import _root_.java.sql.{Connection, DriverManager}
import _root_.code.model._
import _root_.code.db._
import net.liftweb.mongodb.DefaultMongoIdentifier
import java.util.Locale

/**
 * A class that's instantiated early and run.  It allows the application
 * to modify lift's environment
 */
class Boot {
  def boot {
    SlimstackDemoDB.setup

    // where to search snippet
    LiftRules.addToPackages("code")

    // Build SiteMap
    /*
    val entries = Menu(Loc("Home", List("index"), "Home")) ::
      Menu(Loc("Static", Link(List("static"), true, "/static/index"), "Static Content")) ::
      Menu(Loc("Pets", Link(List("pet"), true, "/pet"), "Pets")) ::
      Menu(Loc("Pet edit", Link(List("petedit"), false, "/pet/edit"), "Pet edit")) ::
      User.sitemap
    */

    //LiftRules.setSiteMap(SiteMap(entries:_*))
    LiftRules.setSiteMapFunc(MenuInfo.sitemap)

    /*
    * Show the spinny image when an Ajax call starts
    */
    LiftRules.ajaxStart =
      Full(() => LiftRules.jsArtifacts.show("ajax-loader").cmd)

    /*
    * Make the spinny image go away when it ends
    */
    LiftRules.ajaxEnd =
      Full(() => LiftRules.jsArtifacts.hide("ajax-loader").cmd)

    LiftRules.early.append(makeUtf8)
    LiftRules.loggedInTest = Full(() => User.loggedIn_?)

    //       LiftRules.localeCalculator = localeCalculator _
  }

  /**
   * Force the request to be UTF-8
   */
  private def makeUtf8(req: HTTPRequest) {
     req.setCharacterEncoding("UTF-8")
  }

  private def localeCalculator(request : Box[HTTPRequest]): Locale =
    User.currentUser.map(u => new Locale(u.locale.value)) openOr Locale.getDefault
}

object MenuInfo {
import Loc._

  def sitemap() = SiteMap(
    Menu("Home") / "index",
    Menu("Pet") / "pet" submenus(
      Menu("Edit Pet") / "pet" / "edit")
      )

  /*
      Menu("Ajax Samples") / "ajax",
      Menu("Ajax Form") / "ajax-form",
      Menu("Modal Dialog") / "rhodeisland",
      Menu("JSON Messaging") / "json",
      Menu("Stateless JSON Messaging") / "stateless_json",
      Menu("More JSON") / "json_more",
      Menu("Ajax and Forms") / "form_ajax") ,
    Menu("Persistence") / "persistence" >> noGAE submenus (
      Menu("XML Fun") / "xml_fun" >> noGAE,
      Menu("Database") / "database" >> noGAE,
      Menu(Loc("simple", Link(List("simple"), true, "/simple/index"), "Simple Forms", noGAE)),
      Menu("Templates") / "template" >> noGAE),
    Menu("Templating") / "templating" / "index" submenus(
      Menu("Surround") / "templating" / "surround",
      Menu("Embed") / "templating" / "embed",
      Menu("Evalutation Order") / "templating" / "eval_order",
      Menu("Select <div>s") / "templating" / "selectomatic",
      Menu("Simple Wizard") / "simple_wizard",
      Menu("Lazy Loading") / "lazy",
      Menu("Parallel Snippets") / "parallel",
      Menu("<head/> tag") / "templating"/ "head"),
    Menu("Web Services") / "ws" >> noGAE,
    Menu("Localization") / "lang",
    Menu("Menus") / "menu" / "index" submenus(
      Menu("First Submenu") / "menu" / "one",
      Menu("Second Submenu (has more)") / "menu" / "two" submenus(
        Menu("First (2) Submenu") / "menu" / "two_one",
        Menu("Second (2) Submenu") / "menu" / "two_two"),
      Menu("Third Submenu") / "menu" / "three",
      Menu("Forth Submenu") / "menu" / "four"),
    Menu(WikiStuff),
    Menu("Misc code") / "misc" submenus(
      Menu("Number Guessing") / "guess",
      Menu("Wizard") / "wiz",
      Menu("Wizard Challenge") / "wiz2",
      Menu("Simple Screen") / "simple_screen",
      Menu("Variable Screen") / "variable_screen",
      Menu("Arc Challenge #1") / "arc",
      Menu("File Upload") / "file_upload",
      Menu(Loc("login", Link(List("login"), true, "/login/index"),
               <xml:group>Requiring Login<strike>SiteMap</strike> </xml:group>)),
      Menu("Counting") / "count"),
    Menu(Loc("lift", ExtLink("http://liftweb.net"),
             <xml:group> <i>Lift</i>project home</xml:group>)))*/

}
