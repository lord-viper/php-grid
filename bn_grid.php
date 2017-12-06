<?php
/**
coded by mobin ghasempoor
*/
class bn_grid
{
    private $fields      = array();
    public  $sticky      = false;
    public  $name        = 'bn_grid';
    public  $enable_head = true;
    public  $unicue_col  = '';
    public  $caption     = '';
    public  $editable    = false;
    public  $empty       = '';
    public  $direction   = 'ltr';
    public  $height      = 0;
    private $delete;
    private $paging;
    private $select;
    public  $cssclass;
    public  $color_hover;
    public  $border;

    private function arg2array($args){

        if(is_string($args)){
            parse_str($args,$ret);
            $args = $ret;
        }
        return $args;
    }

    public function delete($caption='delete',$postback=false,$msg='do you want delete this record?',$args='')
    {
        $caption      = empty($caption)?'delete':$caption;
        if(!empty($args)){
            $args     = self::arg2array($args);
            $args     = $args+array('type'=>'submit','value'=>$caption);
            $args     = array_map(function($v,$k){return "$k='$v'";},$args,array_keys($args));
            $args     = implode(' ',$args);
        }
        $this->delete = (object)array('caption'=>$caption,'postback'=>$postback,'msg'=>$msg,'attr'=>$args);
        return $this;
    }

    public function paging($postback=false,$count=15,$args=array())
    {
        $this->paging = (object)array('postback'=>$postback,'count'=>$count,'args'=>$args);
        return $this;
    }

    public function select($caption='select',$postback=false,$args ='')
    {
        $caption      = empty($caption)?'select':$caption;
        if(!empty($args)){
            $args     = self::arg2array($args);
            $args     = $args+array('type'=>'submit','value'=>$caption);
            $args     = array_map(function($v,$k){return "$k='$v'";},$args,array_keys($args));
            $args     = implode(' ',$args);
        }
        $this->select = (object)array('caption'=>$caption,'postback'=>$postback,'attr'=>$args);
        return $this;
    }

    public function column($field,$caption='',$editable=false,$template='')
    {
        $this->fields[$field] = (object)array('field'=>$field,'caption'=>empty($caption)?$field:$caption,'editable'=>$editable,'template'=>$template);
        if($editable == true)
        $this->editable = true;
        return $this;
    }

    private function create_header()
    {
        $out="\n<thead>
              <tr>\n";
        foreach($this->fields as $field)
        {$out.="<th>$field->caption</th>\n";}

        if(isset($this->select))
        $out.="<th>{$this->select->caption}</th>";
        if(isset($this->delete))
        $out.="<th>{$this->delete->caption}</th>\n";
        $out.='</tr>
               </thead>';
       return $out;
    }

    private function create_footer($page,$last)
    {
        $css  = (!empty($this->paging->args['cssclass']))?" class='{$this->paging->args['cssclass']}'":'';
        $btn  = isset($this->paging->args['caption'])?$this->paging->args['caption']:'<<,<,>,>>';
        $btn  = explode(',',$btn);
        $cnt  = count($this->fields);
        if(isset($this->delete))
        $cnt++;
        if(isset($this->select))
        $cnt++;
        $grid = "<tfoot>\n<tr>\n<td colspan='$cnt'>";

        if ($page == 0){

            $grid .= "\t<input type='submit' value='{$btn[0]}' name='{$this->name}_pageing[]'$css disabled>\n";
            $grid .= "\t<input type='submit' value='{$btn[1]}' name='{$this->name}_pageing[]'$css disabled>\n";
        } else{
            $grid .= "\t<input type='submit' value='{$btn[0]}' name='{$this->name}_pageing[0]'$css>\n";
            $grid .= "\t<input type='submit' value='{$btn[1]}' name='{$this->name}_pageing[" . ($page -1) . "]'$css>\n";
        }
        $cnt = isset($this->paging->args['len'])?$this->paging->args['len']:2;

        $fi  = $mi1 = $page;
        if($page > $cnt){
            $mi1 = $page - $cnt;
            $fi  = $cnt-1;
        }
        $mi2 = $page + $cnt;
        $mi3 = $last - $cnt;
        if($page>$mi3)
            $mi2 = $mi3 = $page;
        else
        $mi3++;

        $arr = array_merge(range(0,$fi),range($mi1,$mi2),range($mi3,$last));
        $arr = array_unique($arr);
        $pre = 0;
        foreach($arr as $i)
        {
            $grid.= ($i==$pre?'':'...')."\t<input type='submit' name='{$this->name}_pageing[$i]'".($page==$i++?' disabled':'')." value='$i'$css>\n";
            $pre  = $i;
        }
        if ($last == $page || ($page == 0 && $last == 0)){

            $grid .= "\t<input type='submit' value='{$btn[2]}' name='{$this->name}_pageing[]'$css disabled>\n";
            $grid .= "\t<input type='submit' value='{$btn[3]}' name='{$this->name}_pageing[]'$css disabled>\n";
        } else{
            $grid .= "\t<input type='submit' value='{$btn[2]}' name='{$this->name}_pageing[" . ($page +1) . "]'$css>\n";
            $grid .= "\t<input type='submit' value='{$btn[3]}' name='{$this->name}_pageing[$last]'$css>\n";
        }

        return $grid . "\t<input type='hidden' name='{$this->name}_page' id='{$this->name}_page' value='$page'/>\n</td>\n</tr>\n</tfoot>";
    }

    public function show($datasource,$echo = true)
    {
        if(empty($this->unicue_col)and(!empty($this->delete)or $this->editable))
        trigger_error("you most select unique_col property for delete and editable", E_USER_ERROR);

        $this->direction= 'ltr';
        if(isset($this->cssclass))
        $this->cssclass = " class='$this->cssclass'";
        if(isset($this->border))
        $this->border   = "border='$this->border'";
        $out  = "<table name='$this->name' id='$this->name' $this->border dir='$this->direction'$this->cssclass>";

        if(!empty($datasource))
        {
            $ds     = current($datasource);
            $is_obj = is_object($ds)?true:false;
            $ds     = (array)$ds;
            $col    = array_keys($ds);

            if(empty($this->fields))
            {
                $cnt = count($col);
                for($i=0;$i<$cnt;$i++)
                {$this->fields[$col[$i]] = (object)array('field'=>$col[$i],'caption'=>isset($cap[$i])?$cap[$i]:$col[$i],'editable'=>false,'template'=>'');}
            }

            if(!empty($this->caption))
            $out   .= "<caption>$this->caption</caption>";
            if($this->enable_head)
            $out   .= self::create_header();
            $footer = '';
            if(isset($this->paging))
            {
                $j    = count($datasource);
                $last = intval($j/$this->paging->count);
                $rem  = $j%$this->paging->count;
                if($rem==0)
                --$last;
                $page = isset($_POST["{$this->name}_pageing"])?key($_POST["{$this->name}_pageing"]):
                ((isset($_POST["{$this->name}_page"])and is_numeric($_POST["{$this->name}_page"]))?$_POST["{$this->name}_page"]:0);

                $datasource = array_slice($datasource,$page*$this->paging->count,$this->paging->count);
                $footer     = self::create_footer($page,$last);
            }
            $out .= "\n<tbody>\n";
            foreach($datasource as $row)
            {
                if($is_obj)
                $row  = (array)$row;
                $out .= empty($this->unicue_col)?"<tr>\n":"<tr data-id='{$row[$this->unicue_col]}'>\n";
                //****************************************************************************************************************
                foreach($this->fields as $field=>$val)
                {
                    $edit = $val->editable?" data-edit='$field'":'';
                    if(!empty($val->template))
                    {
                        $template = $val->template;
                        preg_match_all('/<%([^\(%]+)\(?([^\)]*?)?\)?%>/i',$template,$matches);
                        //preg_match_all('/<%(.*?)%>/', $template, $matches);
                        if(!empty($matches[1])){
                            foreach($matches[1] as $key=>$match)
                            {
                                if (!empty($matches[2][$key]))
                                {
                                    $args  = explode(',',$matches[2][$key]);
                                    $param = array();
                                    foreach($args as $arg){
                                        $param[]=in_array($arg,$col)?$row[$arg]:$arg;
                                    }
                                    $template = str_replace($matches[0][$key], call_user_func_array($match,$param), $template);
                                }else
                                    $template=str_replace($matches[0][$key],$row[$match],$template);
                            }
                        }
                        $out .= "<td$edit>$template</td>";
                    }else
                        $out .= "\t<td$edit>$row[$field]</td>\n";
                }//*******************************************************

                if(isset($this->select))
                    $out.= "\t<td><input name='{$this->name}_select[{$row[$this->unicue_col]}]' value='{$this->select->caption}'{$this->select->attr}/></td>\n";

                if(isset($this->delete))
                {
                    $msg  = $this->delete->postback?" onclick=\"return confirm('{$this->delete->msg}')\"":'';
                    $out .= "\t<td><input name='{$this->name}_del[{$row[$this->unicue_col]}]' value='{$this->delete->caption}'{$this->delete->attr}$msg/></td>\n";
                }
                $out .= "</tr>\n";
            }
            $out .= "</tbody>\n$footer";
        }else
        $out  .= "<caption>$this->empty</caption>";
        $out.= "\n</table>";
        if($echo)
        echo $out;
        else
        return $out;
    }

    public function script($args=array())
    {//send_id,page,action
        $args       = self::arg2array($args);
        $arg1 = array(
        'page'    => 'window.location.href',
        'action'  =>'cmd',
        'send_id' =>"#$this->name",
        'edit'    =>'');

        $args = $args+$arg1;
        $out        = '';

        if(!empty($this->editable))
        {
            $edit_func = '';
            if($args['edit']=='parent')
                $edit_func = "jQuery('#$this->name').parent().html(data);";
            elseif($args['edit']=='this')
                $edit_func = "jQuery('#$this->name td[contenteditable=\"true\"]').html(data);";

            $out .= "jQuery(document).on('dblclick','#$this->name tbody td[data-edit]',function(){
            jQuery(this).attr('contenteditable','true');
            jQuery(this).focus();
            })

            jQuery(document).on('keydown','#$this->name tbody td[data-edit]',function(e){

                if(e.keyCode == 13 && !e.shiftKey){

                    var id = jQuery(this).closest('tr').data('id');
                    jQuery.post({$args['page']},{{$args['action']}:'update_edit',col:jQuery(this).data('edit'),val:jQuery(this).text(),id:id},function(data){
                        $edit_func
                        jQuery('#$this->name td[contenteditable]').removeAttr('contenteditable');
                    });
                }
            });\n";
        }

        if(isset($this->select)and!$this->select->postback)
        {
            $out.="\njQuery(document).on('click','input[name^={$this->name}_select]',function(){
                  var num = jQuery(this).attr('name').match(/\[(.*?)\]$/);
                  jQuery.post({$args['page']},{{$args['action']}:'select',id:num[1]}).
                  done(function(data){jQuery('#$this->name').parent().html(data);});
                  });";
        }

        if(isset($this->delete)and!$this->delete->postback)
        {
            $out.="\njQuery(document).on('click','input[name^={$this->name}_del]',function(){
                if(confirm('{$this->delete->msg}'))
                {
                    var num = jQuery(this).attr('name').match(/\[(.*?)\]$/);
                    jQuery.post({$args['page']},{{$args['action']}:'delete',id:num[1]},function(data){
                        jQuery('#$this->name').parent().html(data);
                    });
                }
            });\n";
        }

        if(isset($this->paging)and!$this->paging->postback)
        {
            $out.="\njQuery(document).on('click','input[name^=pageing]',function(){
                 var num = jQuery(this).attr('name').match(/\[(\d+)\]$/);
                 jQuery('#{$this->name}_page').val(num[1]);
                 jQuery.post({$args['page']},'{$args['action']}=paging&'+jQuery('{$args['send_id']} :input').serialize(),function(data){
                    jQuery('#$this->name').parent().html(data);
                 });
                 });\n";
        }

        if($this->sticky)
            $out .= "jQuery('#$this->name thead,#$this->name tbody').css('display','block');
                     jQuery('#$this->name thead').css({'position':'sticky','top':'0px'});";

        if($this->height>0)
            $out .= "jQuery('#$this->name thead,#$this->name tbody,#$this->name tfoot').css('display','block');
                     jQuery('#$this->name tbody').css({'overflow-x':'hidden','overflow-y':'auto','height':'{$this->height}px'});";

        if(isset($this->color_hover))
            $out.= "$(document).on('mouseenter', '#$this->name tr', function () {
                        if(jQuery(this).parent().is('tbody'))
                        jQuery(this).css('background-color','$this->color_hover');
                    }).on('mouseleave', '#$this->name tr', function () {
                        jQuery(this).css('background-color','');
                    });\n";

        return $out;
    }
}
?>