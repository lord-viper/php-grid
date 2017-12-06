<?php
	include_once('../bn_grid.php');
    $rows = array(
    array('id'=>'1','name'=>'mehran','age'=>'14','date'=>'2017-10-12'),
    array('id'=>'2','name'=>'jamshid','age'=>'52','date'=>'2017-10-13'),
    array('id'=>'3','name'=>'mori','age'=>'36','date'=>'2017-13-12'),
    array('id'=>'4','name'=>'peyman','age'=>'47','date'=>'2017-11-11'),
    array('id'=>'5','name'=>'sara','age'=>'21','date'=>'2017-12-18'),
    array('id'=>'6','name'=>'mirta','age'=>'10','date'=>'2017-07-29'),
    array('id'=>'7','name'=>'mehrsam','age'=>'70','date'=>'2017-01-01'),
    array('id'=>'8','name'=>'hana','age'=>'69','date'=>'2017-03-28'),
    array('id'=>'9','name'=>'manan','age'=>'33','date'=>'2016-10-05'),
    array('id'=>'10','name'=>'sogand','age'=>'38','date'=>'2015-11-09'),
    array('id'=>'11','name'=>'samir','age'=>'25','date'=>'2016-11-11'),
    );

    function new_date($date){
        return str_replace('-','/',$date);
    }
    $grid = new bn_grid;
    $grid->column('id','stu_id')->
    column('name','stu_name')->
    column('age','stu_age')->
    column('date','stu_date',false,'<%new_date(date)%>')->
    show($rows);
?>