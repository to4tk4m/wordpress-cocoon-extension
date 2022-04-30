<?php
if ( !defined( 'ABSPATH' ) ) exit;

//ウィジェットエントリーカードリンクタグの取得
if ( !function_exists( 'get_widget_entry_card_link_tag_extension' ) ):
function get_widget_entry_card_link_tag_extension($atts){
  extract(shortcode_atts(array(
    'prefix' => WIDGET_NEW_ENTRY_CARD_PREFIX,
    'url' => null,
    'title' => null,
    'snippet' => null,
    'thumb_size' => null,
    'image_attributes' => null,
    'ribbon_no' => null,
    'type' => null,
    'classes' => null,
    'object' => 'post',
    'object_id' => null,
  ), $atts));
  $class_text = null;
  if (isset($classes[0]) && !empty($classes[0])) {
    $class_text = ' '.implode(' ', $classes);
  }
  //リボンタグの取得
  $ribbon_tag = get_navi_card_ribbon_tag($ribbon_no);
  ob_start(); ?>
  <a href="<?php echo esc_url($url); ?>" class="<?php echo $prefix; ?>-entry-card-link widget-entry-card-link a-wrap<?php echo $class_text; ?>" title="<?php echo esc_attr($title); ?>">
    <div class="<?php echo $prefix; ?>-entry-card widget-entry-card e-card cf">
      <?php echo $ribbon_tag; ?>
      <figure class="<?php echo $prefix; ?>-entry-card-thumb widget-entry-card-thumb card-thumb">
        <?php
        //投稿の場合
        if ($object === 'post' && !empty($object_id)) {
          $class = $prefix.'-entry-card-image widget-entry-card-image card-thumb';
          if ($type === ET_DEFAULT) {
            $size = THUMB120;
          } else {
            $size = THUMB320;
          }
          $attr = array();
          $attr['class'] = $class;

          echo get_the_post_thumbnail( $object_id, $size, $attr );
        } elseif( !empty($image_attributes) && !empty($title) ) {
          if ($object === 'category') {
            //カテゴリーの場合
            $class = 'category-image '.$class;
          } else {
            //NO IMAGEの場合
            $class = 'no-image '.$class;
          }

          echo get_navi_entry_card_thumbnail_tag($image_attributes, $title, $class);
        } else {
          //新着記事・関連記事など
          echo get_widget_entry_card_thumbnail_tag($prefix, $thumb_size, $type);
        }
        ?>
      </figure><!-- /.entry-card-thumb -->

      <div class="<?php echo $prefix; ?>-entry-card-content widget-entry-card-content card-content">
        <div class="<?php echo $prefix; ?>-entry-card-title widget-entry-card-title card-title"><?php echo $title;?></div>
        <?php if ($snippet): ?>
        <div class="<?php echo $prefix; ?>-entry-card-snippet widget-entry-card-snippet card-snippet"><?php echo $snippet; ?></div>
        <?php endif; ?>
        <?php
        if (!is_widget_navi_entry_card_prefix($prefix)) {
          generate_widget_entry_card_date($prefix);
        } ?>
      </div><!-- /.entry-content -->
    </div><!-- /.entry-card -->
  </a><!-- /.entry-card-link -->
<?php
  return ob_get_clean();
}
endif;

//階層化全カテゴリラジオボタンリストの出力
if ( !function_exists( 'generate_all_category_radio_list' ) ):
function generate_all_category_radio_list( $name, $check, $width = 0 ) {
  ob_start();
  if ($width == 0) {
    $width = 'auto';
  } else {
    $width = $width.'px';
  }
  $checked = '';
  $check_no = (int)$check;
  if ($check_no == 0) {
    $checked = ' checked';
  }
  $id = $name.'_0';
  echo '<div class="tab-content category-check-list '.$name.'-list" style="width: '.$width.';">';
  echo '<ul><li><input type="radio" name="'.$name.'" id="'.$id.'" value="0"'.$checked.'><label for="'.$id.'">トップ</label>';
  parent_category_radio_list( 0, $name, $check );
  echo '</li></ul>';
  echo '</div>';

  $res = ob_get_clean();
  echo apply_filters('admin_input_form_tag', $res, $name);
}
endif;

//階層化カテゴリラジオボタンリストの出力の再帰関数
if ( !function_exists( 'parent_category_radio_list' ) ):
function parent_category_radio_list( $parent, $name, $check ) {

  $atts = array (
    'hide_empty' => false,
    'parent'     => $parent,
  );
  $next = get_categories($atts);

  if( $next ) :
    foreach( $next as $cat ) :
      $checked = '';
      $check_no = (int)$check;
      if ($cat->term_id == $check_no) {
        $checked = ' checked';
      }
      $id = $name.'_'.$cat->term_id;
      echo '<ul><li><input type="radio" name="'.$name.'" id="'.$id.'" value="'.$cat->term_id.'"'.$checked.'><label for="'.$id.'">' . $cat->name . '</label>';
      parent_category_radio_list( $cat->term_id, $name, $check );
      echo '</li></ul>'; echo "\n";
    endforeach;
  endif;

}
endif;
