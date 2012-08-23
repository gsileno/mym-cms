<?php 

// load configurations
require_once('../app/config/config.php');

define(MYM_RELATIVE_PATH, "../");

// link MyM
require_once("../core/baseMyM.php");
// boot MyM
MyMboot('../');
   
require_once(MYM_PATH.'/ext/simpletest/autorun.php');
MyMinclude("/core/txtDB.php"); 
    
class TxtdbTest extends UnitTestCase {
  
  function TxtdbTest() {
    $this->UnitTestCase('Txtdb Test');
  }
	
  // Always called before every test function
  function setUp() {
  }
  
  // Always called after every test function
  function tearDown() {
  }
	
  ///// All functions starting with "test" will be tested /////
  function testTxtdb() {
    $testdb = new Txtdb(array('name' => 'db2', 'path' => MYM_PATH.'/application/txtDB'));
    $this->assertIsA($testdb->list_tables(), "array");
    $this->assertFalse($testdb->create_table()); 
    $this->assertFalse($testdb->table_exists("table1")); 
    $this->assertTrue($testdb->create_table("table1", array("field1", "field2")));
    $this->assertTrue($testdb->table_exists("table1")); 
    $this->assertTrue($testdb->rename_table("table1", "table2"));
    $this->assertFalse($testdb->table_exists("table1"));
    $this->assertTrue($testdb->table_exists("table2")); 
    $this->assertTrue($testdb->drop_table("table2"));
    $this->assertFalse($testdb->table_exists("table2")); 
  }
  
  function testTxttable() {
    $testdb = new Txtdb(array('name' => 'db2', 'path' => MYM_PATH.'/application/txtDB'));
    $this->assertTrue($testdb->create_table("table1", array("name", "post")));
    $this->assertEqual($testdb->count_all("table1"), 0);
    $this->assertIsA($testdb->get("table1"), "array");
    $this->assertEqual($testdb->insert("table1", array("name" => "Giovanni", "post" => "Mi piace molto scrivere..")), 1);
    $this->assertEqual($testdb->count_all("table1"), 1);
    $this->assertEqual($testdb->insert("table1", array("name" => "Giovanni 2", "post" => "Questo � il mio secondo post..")), 2);
    $this->assertEqual($testdb->count_all("table1"), 2);    
    $testdb->where("\$id == 1");
    $this->assertTrue($testdb->update("table1", array("name" => "Giovanni bis", "post" => "Mi piace molto scrivere.. e rivedo spesso quello che ho scritto")));
    $this->assertEqual($testdb->count_all("table1"), 2);
    $this->assertTrue($testdb->drop_table("table1"));
    $this->assertTrue($testdb->create_table("table2", array("f1", "f2")));
    for ($i = 0; $i<10; $i++) {
      $testdb->insert("table2", array("f1" => 'A', "f2" => 9-$i));
      $testdb->insert("table2", array("f1" => 'A', "f2" => $i));
    }
    $testdb->where("\$f2 == ".rand(0, 9));
    $this->assertEqual($testdb->update("table2", array("f1" => "Z", "f2" => "changed")), 2);
    $this->assertEqual($testdb->count_all("table2"), 20);
    $this->assertTrue($testdb->drop_table("table2"));
  } 
}

// Full documentation at http://simpletest.org/en/overview.html

/*
assertTrue($x)                    // Fail if $x is false 
assertFalse($x)                   // Fail if $x is true 
assertNull($x)                    // Fail if $x is set 
assertNotNull($x)                 // Fail if $x not set 
assertIsA($x, $t)                 // Fail if $x is not the class or type $t 
assertNotA($x, $t)                // Fail if $x is of the class or type $t 
assertEqual($x, $y)               // Fail if $x == $y is false 
assertNotEqual($x, $y)            // Fail if $x == $y is true 
assertWithinMargin($x, $y, $m)    // Fail if abs($x - $y) < $m is false 
assertOutsideMargin($x, $y, $m)   // Fail if abs($x - $y) < $m is true 
assertIdentical($x, $y)           // Fail if $x == $y is false or a type mismatch 
assertNotIdentical($x, $y)        // Fail if $x == $y is true and types match 
assertReference($x, $y)           // Fail unless $x and $y are the same variable 
assertClone($x, $y)               // Fail unless $x and $y are identical copies 
assertPattern($p, $x)             // Fail unless the regex $p matches $x 
assertNoPattern($p, $x)           // Fail if the regex $p matches $x 
expectError($x)                   // Swallows any upcoming matching error 
assert($e)                        // Fail on failed expectation object $e
*/

/*
setReturnValue($method, $returns, $expectedArgs)
setReturnValueAt($callOrder, $method, $returns, $expectedArgs)
setReturnReference($method, $returns, $expectedArgs)
setReturnReferenceAt($callOrder, $method, $returns, $expectedArgs)
*/

/*
Expectation                              Needs tally()

expect($method, $args)                   No
*/