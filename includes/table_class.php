<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Mehr_List_Table extends WP_List_Table {
    var $example_data = array();
    //*************************************************************************
    function __construct(){
        global $status, $page;
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'movie',     //singular name of the listed records
            'plural'    => 'movies',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }
    //*************************************************************************
    function column_default($item, $column_name){
        switch($column_name){
            //case 'rating':
           // case 'director':
            //    return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    //*************************************************************************
    function column_title($item){
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="post.php?post=%s&action=edit">Edit</a>',$item['ID']),
            //'edits'      => sprintf('<a href="?page=%s&action=%s&movie=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
           'delete'    =>'<a href="'.get_delete_post_link($item['ID']).'">Delete</a>',
           'view'    =>'<a href="'.get_the_permalink($item['ID']).'">View</a>',
       );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    //*************************************************************************
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }
    //*************************************************************************
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => 'عنوان',
            //'rating'    => 'Rating',
            //'director'  => 'Director'
        );
        return $columns;
    }
    //*************************************************************************
    function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false),     //true means it's already sorted
            //'rating'    => array('rating',false),
            //'director'  => array('director',false)
        );
        return $sortable_columns;
    }
    //*************************************************************************
    function get_bulk_actions() {
        $actions = array(
           // 'delete'    => 'Delete'
        );
		//echo $this->show_form();
        return $actions;
    }
    //*************************************************************************
	function show_form() {
		?>
    <label for="bulk-action-selector-top" class="screen-reader-text">انتخاب Post Type</label>
    <!-------------------->
    <select id="bulk-action-selector-top" name="post_type">
    <option value="-1">انتخاب Post Type</option>
    <?php global $wp_post_types; $ptype=array();$PostType=array_keys($wp_post_types);
	if(isset($site['ptype'])) foreach($site['ptype'] as $f) $ptype[]=$f;
	$PostType= array_diff($PostType, array('attachment', 'revision','nav_menu_item'));
	foreach($PostType as $pt){?><option value="<?php echo $pt;?>" <?php echo ((isset($_GET["post_type"]) and $_GET["post_type"]==$pt)?"selected":''); ?>><?php echo $pt;?></option><?php } ?>
    </select>
    <?php //echo $this->months_dropdown("post");?>
    <select name="month">
        <option value="-1">انتخاب ماه</option>
    <?php foreach(range(1,12) as $_year ) : ?>
        <option value="<?php echo $_year;?>" <?php echo ((isset($_GET["month"]) and $_GET["month"]==$_year)?"selected":''); ?>><?php echo date("F", mktime(null, null, null, $_year, 1)) ; ?></option>
    <?php endforeach; ?>
    </select>
    <!-------------------->
    <select name="year">
        <option value="-1">انتخاب سال</option>
    <?php foreach( range(0,10) as $_year ) : ?>
        <option value="<?php echo date("Y", strtotime('-'.$_year.' year')) ; ?>" <?php echo ((isset($_GET["year"]) and $_GET["year"]==date("Y", strtotime('-'.$_year.' year')))?"selected":''); ?>>
		<?php echo date("Y", strtotime('-'.$_year.' year')) ; ?></option>
    <?php endforeach; ?>
    </select>
    <!-------------------->
    <select name="action" id="bulk-action-selector-top">
        <option value="-1">کار دسته جمعی</option>
        <option value="show">نمایش نتیجه</option>
        <option value="delete">حذف همه</option>
    </select>
    <!-------------------->
    <input type="submit" id="doaction" class="button action" value="اجرا">
	<?php 
    }
    //*************************************************************************
        function process_bulk_action() {
        $temp=array();
        if('show'===$this->current_action()){
			$data = new WP_Query(array('showposts'=>'-1','post_type'=>$_GET["post_type"],'date_query' => array('year'  => $_GET["year"],'month' => $_GET["month"])));
			while ($data->have_posts()):$data->the_post();$temp[]=array('ID'=>get_the_id(),'title'=>get_the_title());endwhile;
			$this->example_data=$temp;
        }
		elseif('delete'===$this->current_action()){
			$data = new WP_Query(array('showposts'=>'-1','post_type'=>$_GET["post_type"],'date_query' => array(array('year'  => $_GET["year"],'month' => $_GET["month"]))));
			$all=0;
			while ($data->have_posts()):$data->the_post();
			if($this->delete_associated_media(get_the_id())==1) $all+=1;
			endwhile;
			echo $all." Attachment Found And Deleted.";
			//$this->example_data=$temp;
        }

    }
    //*************************************************************************
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries
        $per_page = 15;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $data = $this->example_data;
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    //*************************************************************************
	function delete_post_attachments($post_id)
	{
		global $post_type;   
		if($post_type !== 'my_custom_post_type') return;
		global $wpdb;
		$args = array(
			'post_type'         => 'attachment',
			'post_status'       => 'any',
			'posts_per_page'    => -1,
			'post_parent'       => $post_id
		);
		$attachments = new WP_Query($args);
		$attachment_ids = array();
		if($attachments->have_posts()) : while($attachments->have_posts()) : $attachments->the_post();
				$attachment_ids[] = get_the_id();
			endwhile;
		endif;
		wp_reset_postdata();
		if(!empty($attachment_ids)) :
			$delete_attachments_query = $wpdb->prepare('DELETE FROM %1$s WHERE %1$s.ID IN (%2$s)', $wpdb->posts, join(',', $attachment_ids));
			$wpdb->query($delete_attachments_query);
		endif;
	}
    //*************************************************************************
	function delete_associated_media( $post_id ) {

 		if(has_post_thumbnail( $post_id ))
        {
          $attachment_id = get_post_thumbnail_id( $post_id );
          wp_delete_attachment($attachment_id, true);
		  return 1;
        }
		return 0;
		//wp_delete_attachment($id);
		/*$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' =>'any', 'post_parent' => $id ); 
		$attachments = get_posts($args);
		if ($attachments) {
			foreach ( $attachments as $attachment ) {
				echo $attachment->post_title.'<br>';
			}
		}*/
	}
}
//*************************************************************************
function mehr_render_list_page(){
    $testListTable = new Mehr_List_Table();
    $testListTable->prepare_items();
    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"><br/></div>
        <h2>حذف ضمیمه پست ها</h2>
        <form id="movies-filter" method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <?php echo wp_referer_field();?>
            <?php echo $testListTable->show_form();?>
            <?php $testListTable->display() ?>
        </form>
    </div>
	<?php
}