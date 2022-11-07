<?php include 'vendor/autoload.php';


class A{
	private function say($string = "a"){
		echo $string;
	}

}

class B{
	public $bridge;
	public function bridge(){
		$this->bridge->say();
	}
}

echo 'BRIDGE';

$a = new A();
$b = new B();

$b->bridge = $a;

$b->bridge('hello');