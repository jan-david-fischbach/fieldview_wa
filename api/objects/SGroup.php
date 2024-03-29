<?php
class SGroup{

    // database connection and table name
    private $conn;
    private $table_name = "products";

    // object properties
    public $id;
    public $pos;
    public $field;
    public $sensor_values;
    public $types;

    public function __construct($db){
      $this->conn = $db;
    }

    function fetch_sensor_values($limit, $time_limit, $start_time){
      $sql = "SELECT SensorID, Type, pos FROM Sensors LEFT JOIN Correction_Sensorposition USING (SensorID) WHERE SGroup = ?";
      $stmt = $this->conn->prepare($sql);
      $stmt->bind_param("s", $this->id);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($SID, $Type, $pos);
      $Types = [];
	    $Pos = [];
      while ($stmt->fetch()) {
        $Types[$SID] = $Type;
		    $Pos[$SID] = $pos;
      }
      $stmt->close();

      $sql = "SELECT Position, Sorte FROM SGroups WHERE SGroup = ?";
      $stmt = $this->conn->prepare($sql);
      $stmt->bind_param("s", $this->id);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($Field_Position, $Plant_Type);
      $stmt->fetch();
      $stmt->close();

	    $pos_type_override = array(1=>"temp1", 2=>"temp2", 3=>"temp3", 4=>"temp4");
      $Result = [];
	    $Result[0] = ["POSITION" => $Field_Position, "PLANTTYPE" => $Plant_Type];
      foreach ($Types as $SID => $Type) {
        if(!isset($this->types) or in_array($Type, $this->types)){
          $sql = "SELECT Timestamp, Value FROM Sensor_Values WHERE SensorID = ? ";
          if($time_limit){
            $sql = $sql."and Timestamp >= DATE_SUB(NOW(),INTERVAL ".$time_limit." SECOND)";
          }elseif ($start_time) {
            $sql = $sql.'and Timestamp >= "'.$start_time.'"';
          }
          if($limit){
            $sql = $sql."ORDER BY id DESC LIMIT ".$limit;
          }
          $stmt = $this->conn->prepare($sql);
          $stmt->bind_param("s", $SID);
          $stmt->execute();
          $stmt->store_result();
          $stmt->bind_result($time, $val);

		  if($Pos[$SID]){
			$Type = $pos_type_override[$Pos[$SID]];
		  }
		  $tmp_arr = ["TYPE" => $Type, "VALUES" => []];
          while ($stmt->fetch()) {
            $tmp_arr["VALUES"][$time] = $val;
          }
          $stmt->close();
          $Result[$SID] = $tmp_arr;
        }
      }

      $this->sensor_values = $Result;
    }

	function set_position($name){
	  $sql = "UPDATE SGroups SET Position=? WHERE SGroup = ?";
      $stmt = $this->conn->prepare($sql);
      $stmt->bind_param("ss", $name, $this->id);
      $stmt->execute();
	}

  function list(){
    // select all query
    $sql = "SELECT * FROM SGroups";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $stmt->store_result();
    return $stmt;
  }
}
?>
