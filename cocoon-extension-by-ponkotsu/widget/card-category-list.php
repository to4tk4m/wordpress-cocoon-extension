<?php
///////////////////////////////////////////////////
//Cocoon用カテゴリ一覧ウイジェットの追加
///////////////////////////////////////////////////
/**
 * Cocoon Extension Category List Widget
 * @author: ponkotsu-web-master
 * @link: https://ponkotsu-web.net/
 */
if ( !defined( 'ABSPATH' ) ) exit;

//カード型カテゴリメニューのショートコード(処理本体)
add_shortcode('cate_list', 'get_category_card_list_tag');
if ( !function_exists( 'get_category_card_list_tag' ) ):
function get_category_card_list_tag($args){
  extract(shortcode_atts(array(
    'child_of'                 => 0,
    'exclude'                  => '',
    'show_count' => 0,
    'show_snippet' => 0,
    'type' => ET_DEFAULT,
    'bold' => 0,
    'arrow' => 0,
    'order'                    => 'ASC',
    'orderby'                  => 'name',
    'id'                       => 0,
    'taxonomy'                 => 'category',
  ), $args, 'cate_list'));

  if (is_admin() && !is_admin_php_page()) {
    return;
  }

  if (!is_cocoon_stylesheet()) {
    echo '<p>Cocoon 以外では使用できません。</p>';
    return;
  }

  $tag = null;
  $atts = array(
//    'child_of'                 => $child_of,
    'exclude'                  => $exclude,
    'hide_empty'               => 0,
    'number'                   => '',
    'order'                    => $order,
    'orderby'                  => $orderby,
    'taxonomy'                 => $taxonomy,
  );
  $atts2 = array();
  //pad_countは取得したカテゴリーの総計になるためparentを指定すると投稿数がとれない
  if (!empty($show_count)) {
    $atts2 = array(
      'pad_counts'               => 1,
    );
  } else {
    $atts2 = array(
      'parent'                   => $id,
    );
  }
  $atts = array_merge($atts, $atts2);
  
  $items = get_categories( $atts );
  if (empty ($items)) {
    echo '<p>'.__( 'カテゴリーは見つかりませんでした。', THEME_NAME ).'</p>';//見つからない時のメッセージ
    return;
  }

  foreach ($items as $item):
    if ($item->category_parent != $id)
      continue;

    //画像情報の取得
    $atts = (object) array(
      'url' => null,
      'object_id' => $item->cat_ID,
      'object' => 'category',
    );
    $image_attributes = get_navi_card_image_attributes($atts, $type);

    $url = get_category_link( $item->cat_ID );
    $title = $item->cat_name;
    if (!empty($show_count)) {
      $title .= '('.$item->category_count.')';
    }
    $snippet = ($show_snippet == 1) ? $item->category_description : null;

    //アイテムタグの取得
    $atts = array(
      'prefix' => 'category',
      'url' => $url,
      'title' => $title,
      'snippet' => $snippet,
      'image_attributes' => $image_attributes,
      'type' => $type,
      'object' => 'category',
    );
    $tag .= get_widget_entry_card_link_tag_extension($atts);
  endforeach;

  //ラッパーの取り付け
  if ($items) {
    $atts = array(
      'tag' => $tag,
      'type' => $type,
      'bold' => $bold,
      'arrow' => $arrow,
      'class' => $class,
    );
    $tag = get_navi_card_wrap_tag($atts);
  }

  return apply_filters('get_category_card_list_tag', $tag);
}
endif;


add_action('widgets_init', function(){register_widget('CocoonExtensionCategoryListWidgetItem');});

if ( !class_exists( 'CocoonExtensionCategoryListWidgetItem' ) ):
class CocoonExtensionCategoryListWidgetItem extends WP_Widget {
  function __construct() {
    parent::__construct(
      'cocoon_extension_category_list',
      COCOON_EXTENSION_WIDGET_NAME.'カテゴリー',
      array('description' => 'ナビカードリストで表示するカテゴリー 一覧です。'),
      array( 'width' => 400, 'height' => 350 )
    );//ウイジェット名
  }
  function widget($args, $instance) {
    if (!is_cocoon_stylesheet()) {
      echo '<p>カテゴリー 一覧はCocoon 以外では使用できません。</p>';
      return;
    }

    extract( $args );
    //タイトル名を取得
    $title = apply_filters( 'category_card_list_widget_title', empty($instance['title']) ? '' : $instance['title'] );
    //表示タイプ
    $entry_type = apply_filters( 'category_card_list_widget_entry_type', empty($instance['entry_type']) ? ET_DEFAULT : $instance['entry_type'] );
    //タイトルの太さ
    $is_bold = apply_filters( 'category_card_list_widget_is_bold', empty($instance['is_bold']) ? 0 : 1 );
    //矢印表示
    $is_arrow_visible = apply_filters( 'category_card_list_widget_is_arrow_visible', empty($instance['is_arrow_visible']) ? 0 : 1 );

    $orderby = apply_filters( 'category_card_list_widget_orderby', empty($instance['orderby']) ? 'name' : $instance['orderby'] );
    $order = apply_filters( 'category_card_list_widget_order', empty($instance['order']) ? 'ASC' : $instance['order'] );
    $show_count = apply_filters( 'category_card_list_widget_count', empty($instance['count']) ? 0 : 1 );
    $show_snippet = apply_filters( 'category_card_list_widget_snippet', empty($instance['snippet']) ? 0 : 1 );
    $top_cat_id = apply_filters( 'category_card_list_widget_top_cat_id', empty($instance['top_cat_id']) ? '0' : $instance['top_cat_id'] );
    //除外カテゴリーIDを取得
    $exclude_cat_ids = empty($instance['exclude_cat_ids']) ? array() : $instance['exclude_cat_ids'];
    $exclude_cat_ids = apply_filters( 'category_card_list_widget_exclude_cat_ids', $exclude_cat_ids, $instance, $this->id_base );

    echo $args['before_widget'];
    if ($title) {
      echo $args['before_title'];
      if ($title) {
        echo $title;//タイトルが設定されている場合は使用する
      }
      echo $args['after_title'];
    }

    //除外カテゴリ配列のサニタイズ
    if (empty($exclude_cat_ids)) {
      $exclude_cat_ids = array();
    } else {
      if (!is_array($exclude_cat_ids)) {
        $exclude_cat_ids = explode(',', $exclude_cat_ids);
      }
    }

    //引数配列のセット
    $atts = array(
      'id' => (int)$top_cat_id,
      'type' => $entry_type,
      'bold' => $is_bold,
      'arrow' => $is_arrow_visible,
      'show_count' => $show_count,
      'order'                    => $order,
      'orderby'                  => $orderby,
      'show_snippet' => $show_snippet,
      'exclude' => $exclude_cat_ids,
    );
    // _v($atts);
    //リストの作成
    echo get_category_card_list_tag($atts);

    echo $args['after_widget'];

  }
  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    if (isset($new_instance['title']))
      $instance['title'] = strip_tags($new_instance['title']);
    if (isset($new_instance['entry_type']))
      $instance['entry_type'] = strip_tags($new_instance['entry_type']);
    if (isset($new_instance['top_cat_id']))
      $instance['top_cat_id'] = strip_tags($new_instance['top_cat_id']);
    if (isset($new_instance['orderby']))
      $instance['orderby'] = strip_tags($new_instance['orderby']);
    if (isset($new_instance['order']))
      $instance['order'] = strip_tags($new_instance['order']);

    $instance['is_bold'] = !empty($new_instance['is_bold']) ? 1 : 0;
    $instance['is_arrow_visible'] = !empty($new_instance['is_arrow_visible']) ? 1 : 0;

    if (isset($new_instance['exclude_cat_ids'])){
      $instance['exclude_cat_ids'] = $new_instance['exclude_cat_ids'];
    } else {
      $instance['exclude_cat_ids'] = array();
    }
    
    $instance['count'] = ! empty( $new_instance['count'] ) ? 1 : 0;
    $instance['snippet'] = ! empty( $new_instance['snippet'] ) ? 1 : 0;


    return $instance;
  }

  function form($instance) {
    if (!is_cocoon_stylesheet()):
      echo '<p>Cocoon 以外では使用できません。</p>';
    else: // is_cocoon_stylesheet() => Cocoonテーマを使っている場合
    if(empty($instance)){
      $instance = array(
        'title' => '',
        'entry_type' => ET_DEFAULT,
        'top_cat_id' => '0',
        'orderby' => 'name',
        'order' => 'ASC',
        'is_bold' => 0,
        'is_arrow_visible' => 0,
        'exclude_cat_ids' => array(),
        'count' => 0,
        'hierarchical' => 0,
      );
    }
    $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
    $entry_type = isset($instance['entry_type']) ? esc_attr($instance['entry_type']) : ET_DEFAULT;
    $top_cat_id = isset($instance['top_cat_id']) ? esc_attr($instance['top_cat_id']) : '0';
    $orderby = isset($instance['orderby']) ? esc_attr($instance['orderby']) : 'name';
    $order = isset($instance['order']) ? esc_attr($instance['order']) : 'ASC';
    $is_bold = !empty($instance['is_bold']) ? 1 : 0;
    $is_arrow_visible = !empty($instance['is_arrow_visible']) ? 1 : 0;
    $exclude_cat_ids = isset($instance['exclude_cat_ids']) ? $instance['exclude_cat_ids'] : array();
    $count = !empty($instance['count']) ? 1 : 0;
    $snippet = !empty($instance['snippet']) ? 1 : 0;
    $hierarchical = !empty($instance['hierarchical']) ? 1 : 0;

    //_v($exclude_cat_ids);
    //var_dump($instance);
    ?>
    <?php //タイトル入力フォーム ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">
        <?php _e( 'タイトル', THEME_NAME ) ?>
      </label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    <p>
      <?php
      generate_label_tag($this->get_field_id('entry_type'), __('表示タイプ', THEME_NAME) );
      echo '<br>';
      $options = get_widget_entry_type_options();
      generate_radiobox_tag($this->get_field_name('entry_type'), $options, $entry_type);
      ?>
    </p>
    <?php //トップカテゴリー ?>
    <p>
      <label>
        <?php _e( 'トップカテゴリー（選択したカテゴリーの直下が抽出されます）', THEME_NAME ) ?>
      </label>
      <?php echo generate_all_category_radio_list($this->get_field_name('top_cat_id'), $top_cat_id); ?>
    </p>
    <?php //並び基準 ?>
    <p>
      <?php
        $params = array (
          'id' => __( 'ID', THEME_NAME ),
          'name' => __( '名前', THEME_NAME ),
          'slug' => __( 'スラグ', THEME_NAME ),
          'count' => __( '投稿数', THEME_NAME ),
          'term_group' => __( 'グループ', THEME_NAME ),
        );
        generate_selectbox_tag($this->get_field_name('orderby'), $params, $orderby, __( '並びの基準', THEME_NAME ));
      ?>
    </p>
    <?php //並び順 ?>
    <p>
      <?php
        $params = array (
          'ASC' => __( '昇順', THEME_NAME ),
          'DESC' => __( '降順', THEME_NAME ),
        );
        generate_selectbox_tag($this->get_field_name('order'), $params, $order, __( '並び順', THEME_NAME ));
      ?>
    </p>
    <?php //カテゴリ数の表示 ?>
    <p>
      <?php
        generate_checkbox_tag($this->get_field_name('count') , $count, __( '投稿数を表示', THEME_NAME ));
      ?>
    </p>
    <?php //「説明」の表示 ?>
    <p>
      <?php
        generate_checkbox_tag($this->get_field_name('snippet') , $snippet, __( '「説明」を表示', THEME_NAME ));
      ?>
    </p>
<!-- ****************
    <?php //階層を表示 ?>
    <p>
      <?php
        generate_checkbox_tag($this->get_field_name('hierarchical') , $hierarchical, __( '階層を表示', THEME_NAME ));
      ?>
    </p>
****************** -->
    <?php //除外カテゴリーID ?>
    <p>
      <label>
        <?php _e( '除外カテゴリーID（除外するものを選択してください）', THEME_NAME ) ?>
      </label>
      <?php echo generate_hierarchical_category_check_list(0, $this->get_field_name('exclude_cat_ids'), $exclude_cat_ids); ?>
    </p>
    <?php //タイトルを太字にする ?>
    <p>
      <?php
        generate_checkbox_tag($this->get_field_name('is_bold') , $is_bold, __( 'タイトルを太字にする', THEME_NAME ));
      ?>
    </p>
    <?php //矢印表示 ?>
    <p>
      <?php
        generate_checkbox_tag($this->get_field_name('is_arrow_visible') , $is_arrow_visible, __( '矢印表示', THEME_NAME ));
      ?>
    </p>
    <?php
    endif;
  }
}
endif;

