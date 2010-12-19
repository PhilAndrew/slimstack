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

