<?php


// $d = 'a:6:{s:5:"width";i:600;s:6:"height";i:600;s:4:"file";s:24:"2022/12/10-600x600-1.jpg";s:8:"filesize";i:57206;s:5:"sizes";a:5:{s:6:"medium";a:5:{s:4:"file";s:24:"10-600x600-1-300x300.jpg";s:5:"width";i:300;s:6:"height";i:300;s:9:"mime-type";s:10:"image/jpeg";s:8:"filesize";i:6762;}s:9:"thumbnail";a:5:{s:4:"file";s:24:"10-600x600-1-150x150.jpg";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:10:"image/jpeg";s:8:"filesize";i:2551;}s:30:"product-search-thumbnail-32x32";a:5:{s:4:"file";s:22:"10-600x600-1-32x32.jpg";s:5:"width";i:32;s:6:"height";i:32;s:9:"mime-type";s:10:"image/jpeg";s:8:"filesize";i:893;}s:29:"woocommerce_gallery_thumbnail";a:5:{s:4:"file";s:24:"10-600x600-1-100x100.jpg";s:5:"width";i:100;s:6:"height";i:100;s:9:"mime-type";s:10:"image/jpeg";s:8:"filesize";i:1600;}s:14:"shop_thumbnail";a:5:{s:4:"file";s:24:"10-600x600-1-100x100.jpg";s:5:"width";i:100;s:6:"height";i:100;s:9:"mime-type";s:10:"image/jpeg";s:8:"filesize";i:1600;}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}';
// print_r(unserialize($d));
// die();

define('_TAXONOMY_SIMPLE', 2);
define('_TAXONOMY_VARIANT', 4);

define('_VARIANCE_COLOR', 1);
define('_VARIANCE_SIZE', 2);
define('_VARIANCE_BOTH', 3);

define('_INSTOCK', 'instock');
define('_OUTSTOCK', 'outofstock');

define('_META_KEEP', 0);
define('_META_UPDATE', 1);
define('_META_REMOVE', 2);




require_once __ROOT_DIR.'/core/webapi.php';	
require_once __ROOT_DIR.'/core/webauth.php';
require_once __ROOT_DIR.'/core/sqlutil.php';	
require_once __ROOT_DIR.'/core/couchdbclient.php';

require_once __DIR__ . '/sync_maps.php';
require_once __DIR__ . '/sync_base.php';
require_once __DIR__ . '/lib-kalistaimage.php';



use \FGTA4\utils\SqlUtility;
use \FGTA4\CouchDbClient;
use \FGTA4\clibase;
use \FGTA4\console;




class SYNC extends SyncBase {

	private $metaTemplate = null;
	private $kie; // kalista image storage

	public function Run() {
		try {

			SqlUtility::setQuote('`', '`');

			$region_id = $this->region_id;
	
			// $output_file = __DIR__ . "/web-$region_id.dat";
			$output_file = implode('/', [$this->tempDir, "/web-$region_id.dat"]);
			$fp = fopen($output_file, "r+");
	
			// baris 1: total baris items
			$total = (int)trim(fgets($fp));
	
			// baris 2: new arrival
			$itemNew = json_decode(base64_decode(fgets($fp)));
	
			// baris 3: discount / promo items
			$itemDisc = json_decode(base64_decode(fgets($fp)));
	
			// baris 4: highlighted produc
			$itemHighlight = json_decode(base64_decode(fgets($fp)));
	
			// baris 5: favourite product
			$itemFave = json_decode(base64_decode(fgets($fp)));
	
	
			// $this->tfiweb_markUpdating($this->brand_id);
	
			$productposts = [];
			$u = 0;
			$i = 0;
			while (!feof($fp)) {
				$line = trim(fgets($fp));
				if ($line=="") { continue; }
	
				$i++;
				
				$mercharticle = json_decode(base64_decode($line), true);	
				if ($mercharticle==null) { continue; }	
			
				$totalqty = (int)$mercharticle['totalqty'];
				if ($totalqty<=self::QTY_THRESHOLD) { continue; } 
				// yang diupdate hanya item-item yang qty nya lebih dari QTY_THRESHOLD
			
	
				if (count($mercharticle['items'])>0) {
					// yang di update yang ada informasi itemsnya saja
					$this->output("processing line $i of $total (".$mercharticle['mercharticle_id'].")", ['nonewline'=>true]);
					if (count($mercharticle['images'])==0) {
						$this->output("");
						continue; // kalau artikel tidak ada image, tidak perlu diupdate
					}
	
					if (!array_key_exists('WP-TAXONOMY-BRAND', $mercharticle['props'])) {
						$this->output("");
						continue; // kalau tidak ada data taxonomi tidak perlu diupdate
					}
	
	
					$u++;
					$result = $this->tfiweb_updateMercharticle($mercharticle);
					
	
					$parent_post_id = $result['parent_post_id'];
					$postlist = $result['postlist'];
					$imagespost = $result['imagespost'];
	
	
					
					if ($totalqty>0) {
						$productposts[] = $parent_post_id;
					}
	
	
					$fn_callback = function($args) {
						$this->output(".", ['nonewline'=>true]);
					};
	
					
					$this->kie->saveall($imagespost, self::IMAGE_SIZES, [
						'targetImagesLocation' => $this->targetImagesLocation,
						'tempImagesLocation' => $this->tempImagesLocation,
						'brand_id' => $this->brand_id
					], $fn_callback );
					
	
					$this->output("");
				}
	
				//print_r($mercharticle);
				//$this->getImage();
				// die("die at line " . __LINE__ . "\r\n");
				
			}
			fclose($fp);
	
			$this->output("\r\nUpdated $u articles\r\n");
	
	
			$data = [];
			$this->tfiweb_updateItemNew($itemNew);
			$this->tfiweb_updateItemDisc($itemDisc);
			$data['highlight'] = $this->tfiweb_updateItemHighlight($itemHighlight, $productposts);
			$this->tfiweb_updateItemFave($itemFave);
		
			$page_post_id =  434; // fkp
			$this->tfiweb_updateBrandFrontPage($page_post_id,  $data);
	
		} catch (\Exception $ex) {
			throw $ex;
		}

	}



	public function tfiweb_updateMercharticle(array $mercharticle) : array {
	
		try {

			$producttype = _TAXONOMY_SIMPLE; // default
			$variantype = null;
			$parent_sku = $mercharticle['mercharticle_id'];
			$isvarcolor = (int)$mercharticle['mercharticle_isvarcolor'];
			$isvarsize = (int)$mercharticle['mercharticle_isvarsize'];
			if ($isvarcolor==0 && $isvarsize==0) {
				// cuma 1 variance
				$parent_sku = $mercharticle['items'][0]['merchitem_id'];
				$producttype = _TAXONOMY_SIMPLE;
				$variantype = null;
			} else {
				// ada pilihan variance
				$producttype = _TAXONOMY_VARIANT;
				$variantype = _VARIANCE_COLOR;  // _VARIANCE_COLOR | _VARIANCE_SIZE | _VARIANCE_BOTH
			}

			$postlist = [];

			$parent_posttype = 'product';
			$parent_post_id = 0;
			$parent_item = $mercharticle['items'][0];
			$parent_args = [
				'parent_post_id' => $parent_post_id, 
				'sku' => $parent_sku,
				'posttype' => $parent_posttype,
				'brand_id' => $mercharticle['brand_id'],
				'content' => $parent_item['merchitem_descr'],
				'shortdescr' => $this->createShortDescr($parent_item, $parent_posttype, $producttype, $variantype),
				'post_name' => $this->textToSlug($mercharticle['brand_id'] . '-item-' . $mercharticle['mercharticle_id']),
				'ordernum' => 0,
				'mercharticle_id' => $mercharticle['mercharticle_id'],
				'itemstock_id' => $mercharticle['mercharticle_id'],
				'minprice' => $mercharticle['minprice'],
				'maxprice' => $mercharticle['maxprice'],
				'grossprice' => $parent_item['grossprice'],
				'sellprice' => $parent_item['sellprice'], 
				'onsale' => $this->getProductSaleStatus(0, $producttype, $parent_item['grossprice'], $parent_item['sellprice']),
				'qty' => $mercharticle['totalqty'],
				'taxclass' => '',
				'images' => &$mercharticle['images'],
				'producttype' => $producttype,
				'variantype' => $variantype
			];

			// create parent 
			// test: TM22100054201
			$post = $this->createPost($parent_item, $parent_args);
			$parent_post_id = $this->tfiweb_updatePost($post, [
				'fn_createupdatepost' => 'createPostUpdateFinal',
				'fn_parameter' => [
					'guidprefix' => $this->TfiWebBaseUrl . '/?post_type=product&#038;p=',
				]
			]);

			$product = $this->createProduct($parent_item, $parent_args, $parent_post_id);
			$this->tfiweb_updateproduct($product);

			$parent_args['parent_post_id'] = $parent_post_id;
			$imagespost = $this->tfiweb_addImagesPost($mercharticle['images'], $parent_args);

			// update parent postmeta
			//print_r(unserialize('a:4:{s:5:"width";i:600;s:6:"height";i:600;s:4:"file";s:37:"merch/fkp/62f5ae838e215/1-600x600.jpg";s:8:"filesize";i:15200;}'));

			$metas = $this->generateParentMetaList($parent_item, $parent_args, $imagespost);
			foreach ($metas as $metakey=>$metadata) {
				$meta = $this->createMeta($parent_post_id, $metakey, $metadata);
				$meta_id = $this->tfiweb_updateMeta($meta, $metadata['update']);
			}



			$firstitem_imagelist = null;
			$firstitem_imagesposts = null;
			$firstitem_galleries = null;

			$postlist[] = $parent_post_id;
			
			$i=0;
			foreach ($mercharticle['items'] as $item) {
				$i++;
				$posttype = 'product_variation';
				$args = [
					'parent_post_id' => $parent_post_id, 
					'sku' => $item['merchitem_id'],
					'posttype' => $posttype,
					'brand_id' => $mercharticle['brand_id'],
					'content' => '',
					'shortdescr' => $this->createShortDescr($item, $posttype, $producttype, $variantype),
					'post_name' => $this->textToSlug($mercharticle['brand_id'] . '-item-' . $item['merchitem_id']),
					'ordernum' => $i,
					'mercharticle_id' => $mercharticle['mercharticle_id'],
					'itemstock_id' => $item['merchitem_id'],
					'minprice' => $item['sellprice'],
					'maxprice' => $item['sellprice'],
					'grossprice' => $item['grossprice'],
					'sellprice' => $item['sellprice'], 
					'onsale' => $this->getProductSaleStatus(1, $producttype, $item['grossprice'], $item['sellprice']),
					'qty' => $mercharticle['totalqty'],
					'taxclass' => 'parent',
					'images' => &$mercharticle['images'],
					'producttype' => $producttype,
					'variantype' => $variantype
				];

				$post = $this->createPost($item, $args);
				$post_id = $this->tfiweb_updatePost($post, [
					'fn_createupdatepost' => 'createPostUpdateFinal',
					'fn_parameter' => [
						'guidprefix' => $this->TfiWebBaseUrl . '/?post_type=product_variation&p=',
					]
				]);
				
				$product = $this->createProduct($item, $args, $post_id);
				$this->tfiweb_updateproduct($product);

				
				$metas = $this->generateChildMetaList($item, $args);
				foreach ($metas as $metakey=>$metadata) {
					$meta = $this->createMeta($post_id, $metakey, $metadata);
					$meta_id = $this->tfiweb_updateMeta($meta, $metadata['update']);
				}
				
				
				$postlist[] = $post_id;
			}
			



			// update tfi_term_relationships
			$properties = $mercharticle['props'];
			$taxonomykeys = ['WP-TAXONOMY-BRAND', 'WP-TAXONOMY-GRO', 'WP-TAXONOMY-CTG'];
			
			foreach ($postlist as $post_id) {
				$previoustermrellist = $this->tfiweb_getCurrentTermRelationship($post_id);
				$currenttermrellist = [];
				$wps_object_type = 'variation';  
				
				if ($post_id==$parent_post_id) {
					$obj = new \stdClass;
					$obj->object_id = $post_id;
					$obj->term_taxonomy_id = $producttype;  // _TAXONOMY_SIMPLE | _TAXONOMY_VARIANT
					$obj->term_order = 0;
					$this->tfiweb_updateTermRelationship($obj);
					$currenttermrellist[] = $obj->term_taxonomy_id;
					$wps_object_type = 'simple'; //TODO: cek produktype, simple, atau yg lainnya
				}

				$terms = []; 
				foreach ($properties as $key=>$propdata) {
					if (!in_array($key, $taxonomykeys)) {
						continue;
					}
	
					$value = $propdata['mercharticleprop_value'];
					$terms[$key] = $value;
				
					$obj = new \stdClass;
					$obj->object_id = $post_id;
					$obj->term_taxonomy_id = $value;
					$obj->term_order = 0;
					$this->tfiweb_updateTermRelationship($obj);
					$currenttermrellist[] = $obj->term_taxonomy_id;
				}

				// cleanup notupdated termrelationsjip
				$this->tfiweb_cleanTermRelationship($post_id, $previoustermrellist, $currenttermrellist);


				// update wps object
				$objs = [];
				$brand = $terms['WP-TAXONOMY-BRAND'];
				$gro = $terms['WP-TAXONOMY-GRO'];
				$ctg = $terms['WP-TAXONOMY-CTG'];

				if ($post_id==$parent_post_id) {
					$objs[] = $this->createWpsObject($post_id, null, 0, null, $wps_object_type, null, null);					
					$objs[] = $this->createWpsObject($post_id, null, $brand, null, $wps_object_type, 'product_cat', 0);					
					$objs[] = $this->createWpsObject($post_id, null, $gro, $brand, $wps_object_type, 'product_cat', 0);					
					$objs[] = $this->createWpsObject($post_id, null, $ctg, $gro, $wps_object_type, 'product_cat', 0);					

				} else {
					$objs[] = $this->createWpsObject($post_id, $parent_post_id, 0, null, $wps_object_type, null, null);					
					$objs[] = $this->createWpsObject($post_id, $parent_post_id, $brand, null, $wps_object_type, 'product_cat', 0);					
					$objs[] = $this->createWpsObject($post_id, $parent_post_id, $gro, $brand, $wps_object_type, 'product_cat', 0);					
					$objs[] = $this->createWpsObject($post_id, $parent_post_id, $ctg, $gro, $wps_object_type, 'product_cat', 0);					

				}

				// $this->tfiweb_cleanWpsObject($post_id);
				foreach ($objs as $obj) {
					$this->tfiweb_updateWpsObject($obj);
				}

			}

			$result = [
				'parent_post_id' => $parent_post_id,
				'postlist' => $postlist,
				'imagespost' => $imagespost

			];

			return $result;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}





	public function createWpsObject(
		int $post_id, ?int $parent_post_id, int $term_id, ?int $parent_term_id, 
		string $wps_object_type, ?string $taxonomy, ?string $inherit) : object
	{
		$obj = new \stdClass;
		$obj->object_id = $post_id;
		$obj->parent_object_id = $parent_post_id;
		$obj->term_id = $term_id;
		$obj->parent_term_id = $parent_term_id;
		$obj->object_type = $wps_object_type;
		$obj->taxonomy = $taxonomy;
		$obj->inherit = $inherit;
		return $obj;
	}


	public function tfiweb_updateWpsObject($obj) {
		try {
			// cek 
			$sqlCek = "
				select *
				from tfi_wps_object_term
				where
				    object_id=:object_id 
				and parent_object_id=:parent_object_id 
				and term_id=:term_id
				and parent_term_id=:parent_term_id 
				and object_type=:object_type 
				and taxonomy=:taxonomy
			";
			$stmtCek = $this->db_tfiweb->prepare($sqlCek);
			$stmtCek->execute([
				':object_id' => $obj->object_id,
				':parent_object_id' => $obj->parent_object_id,
				':term_id' => $obj->term_id,
				':parent_term_id' => $obj->parent_term_id,
				':object_type' => $obj->object_type,
				':taxonomy' => $obj->taxonomy
			]);
			$row = $stmtCek->fetch();
			if ($row==null) {
				$cmd = SqlUtility::CreateSQLInsert("tfi_wps_object_term", $obj);
				$stmtInsert = $this->db_tfiweb->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);	
			}
		
		} catch (\Exception $ex) {
			throw $ex;
		}	
	}

	public function tfiweb_cleanWpsObject(int $post_id) : void {
		try {
			$keys = new \stdClass;
			$keys->object_id = $post_id;
			$cmd = SqlUtility::CreateSQLDelete("tfi_wps_object_term", $keys);
			$stmt = $this->db_tfiweb->prepare($cmd->sql);
			$stmt->execute($cmd->params);

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function createMeta(string $post_id, string $metakey, array $metadata) : object {
		$meta = new \stdClass;
		$meta->post_id = $post_id;
		$meta->meta_key = $metakey;
		$meta->meta_value = $metadata['value'];
		return $meta;
	}

	public function tfiweb_updateMeta(object $meta, int $updatemode) : int {
		$sqlCek = "
			select meta_id, post_id, meta_key, meta_value
			from tfi_postmeta where post_id = :post_id and meta_key = :meta_key
		";	
		$stmtCek = $this->db_tfiweb->prepare($sqlCek);

		try {
			$meta_id = 0;

			$stmtCek->execute([':post_id'=>$meta->post_id, ':meta_key'=>$meta->meta_key]);
			$rowCek = $stmtCek->fetch();
			if ($rowCek==null) {
				$cmd = SqlUtility::CreateSQLInsert("tfi_postmeta", $meta);
				$stmtInsert = $this->db_tfiweb->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);	
			} else {
				$keys = new \stdClass;
				$keys->post_id = $meta->post_id;
				$keys->meta_key = $meta->meta_key;
				
				if ($updatemode==_META_UPDATE) {
					$cmd = SqlUtility::CreateSQLUpdate("tfi_postmeta", $meta, $keys);
					$stmt = $this->db_tfiweb->prepare($cmd->sql);
					$stmt->execute($cmd->params);

				} else if ($updatemode==_META_REMOVE) {
					$cmd = SqlUtility::CreateSQLDelete("tfi_postmeta", $keys);
					$stmt = $this->db_tfiweb->prepare($cmd->sql);
					$stmt->execute($cmd->params);

				}
			}


			return $meta_id;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function generateDefaultMetaList() {
		$metas = [];
		$meta['_backorders'] = ['value'=>'no', 'update'=>_META_KEEP];
		$meta['_download_expiry'] = ['value'=>'-1', 'update'=>_META_KEEP];
		$meta['_download_limit'] = ['value'=>'-1', 'update'=>_META_KEEP];
		$meta['_downloadable'] = ['value'=>'no', 'update'=>_META_KEEP];
		$meta['_manage_stock'] = ['value'=>'no', 'update'=>_META_UPDATE];
		$meta['_price'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['_product_attributes'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['_product_image_gallery'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['_product_version'] = ['value'=>'6.2.2', 'update'=>_META_KEEP];
		$meta['_regular_price'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['_sale_price'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['_sku'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['_sold_individually'] = ['value'=>'no', 'update'=>_META_KEEP];
		$meta['_stock'] = ['value'=>null, 'update'=>_META_UPDATE];
		$meta['_stock_status'] = ['value'=>'instock', 'update'=>_META_UPDATE];
		$meta['_tax_class'] = ['value'=>'', 'update'=>_META_KEEP];
		$meta['_tax_status'] = ['value'=>'taxable', 'update'=>_META_KEEP];
		$meta['_thumbnail_id'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['_variation_description'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['_virtual'] = ['value'=>'no', 'update'=>_META_KEEP];
		$meta['_wc_average_rating'] = ['value'=>'0', 'update'=>_META_KEEP];
		$meta['_wc_review_count'] = ['value'=>'0', 'update'=>_META_KEEP];
		$meta['_wpb_vc_js_status'] = ['value'=>'false', 'update'=>_META_KEEP];
		$meta['total_sales'] = ['value'=>'0', 'update'=>_META_KEEP];
		$meta['wccaf_barcode'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['wccaf_material_code'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['wccaf_material_name'] = ['value'=>'', 'update'=>_META_UPDATE];
		$meta['wccaf_unit_length'] = ['value'=>'', 'update'=>_META_UPDATE];
		return $meta;
	}

	public function setMeta(array &$metas, string $metaname, ?string $value=null, ?int $mode=null) : void {
		if ($this->metaTemplate==null) {
			$this->metaTemplate = $this->generateDefaultMetaList();
		}

		$meta = ['value'=>$value, 'update'=>$mode!=null?$mode:_META_UPDATE];
		if (array_key_exists($metaname, $this->metaTemplate)) {
			$meta = $this->metaTemplate[$metaname];
			if ($value!=null) {
				$meta['value'] = $value;
			}
			if ($mode!=null) {
				$meta['update'] = $mode;
			}
		}

		if (!array_key_exists($metaname, $metas)) {
			$metas[$metaname] = ['value'=>$meta['value'], 'update'=>$meta['update']];
		} else {
			$metas[$metaname]['value'] = $meta['value'];
			$metas[$metaname]['update'] = $meta['mode'];
		}
	}

	public function getImageInfo(array $imagelist, array $imagespost, string $size) : ?array {
		$thumbnail_id = '';
		$gallery = [];
		if (is_array($imagelist) && is_array($imagespost)) {
			
			$i = 0;
			foreach ($imagelist as $key) {
				if (array_key_exists($key, $imagespost)) {
					$posts = $imagespost[$key];
					if (count($posts)>0) {
						if (array_key_exists($size, $posts)) {
							$post_id = $posts[$size]['post_id'];
							if ($i==0) {
								$thumbnail_id = $post_id;
							} else {
								$gallery[] = $post_id;
							}
						}
					}					
				}

				$i++;
			}

			return [
				'thumbnail_id' => $thumbnail_id,
				'image_gallery' => implode(',', $gallery)
			];
		} else {
			return null;
		}
	}


	public function generateParentMetaList(array $item, array $args, array $imagespost) : array {
		$imagesize = "600";
		$thumbnail_id = '';
		$image_gallery = '';

		$imageinfo = $this->getImageInfo($item['imagelist'], $imagespost, $imagesize);
		if (is_array($imageinfo)) {
			$thumbnail_id = $imageinfo['thumbnail_id'];
			$image_gallery = $imageinfo['image_gallery'];
		}
		

		$qty = $args['qty'];
		$onsale = $args['onsale'];
		$stock_status = $qty > 0 ? _INSTOCK : _OUTSTOCK;

		$metas = [];
		$this->setMeta($metas, 'total_sales');
		$this->setMeta($metas, '_wc_average_rating');
		$this->setMeta($metas, '_wc_review_count');
		$this->setMeta($metas, '_tax_status');
		$this->setMeta($metas, '_tax_class');
		$this->setMeta($metas, 'taxable');
		$this->setMeta($metas, '_manage_stock');
		$this->setMeta($metas, '_backorders');
		$this->setMeta($metas, '_sold_individually');
		$this->setMeta($metas, '_virtual');
		$this->setMeta($metas, '_downloadable');
		$this->setMeta($metas, '_download_limit');
		$this->setMeta($metas, '_download_expiry');
		$this->setMeta($metas, '_stock', $qty);
		$this->setMeta($metas, '_stock_status', $stock_status);
		$this->setMeta($metas, '_product_version');
		$this->setMeta($metas, '_product_attributes');
		$this->setMeta($metas, '_wpb_vc_js_status');
		$this->setMeta($metas, '_sku', $args['sku']);
		$this->setMeta($metas, 'wccaf_barcode', '');
		$this->setMeta($metas, 'wccaf_material_code', '');
		$this->setMeta($metas, 'wccaf_material_name', '');
		$this->setMeta($metas, 'wccaf_unit_length', '');
		$this->setMeta($metas, '_regular_price', $args['grossprice']);
		$this->setMeta($metas, '_price', $args['sellprice']);
		$this->setMeta($metas, '_sale_price', $args['sellprice']);
		
		$this->setMeta($metas, '_thumbnail_id', $thumbnail_id); // 5179
		$this->setMeta($metas, '_product_image_gallery', $image_gallery);
												 

		return $metas;
	}

	public function generateChildMetaList(array $item, array $args) : array {
		$imagesize = "600";
		$image_gallery = '';
		$thumbnail_id = '0';

		$qty = $args['qty'];
		$onsale = $args['onsale'];
		$stock_status = $qty > 0 ? _INSTOCK : _OUTSTOCK;

		$metas = [];
		$this->setMeta($metas, '_backorders');
		$this->setMeta($metas, '_download_expiry');
		$this->setMeta($metas, '_download_limit');
		$this->setMeta($metas, '_downloadable');
		$this->setMeta($metas, '_product_version');
		$this->setMeta($metas, '_sold_individually');
		$this->setMeta($metas, '_tax_class');
		$this->setMeta($metas, '_tax_status');
		$this->setMeta($metas, '_virtual');
		$this->setMeta($metas, '_wc_average_rating');
		$this->setMeta($metas, '_wc_review_count');
		$this->setMeta($metas, 'total_sales');

		$this->setMeta($metas, '_manage_stock', 'yes');
		$this->setMeta($metas, '_price', $args['sellprice']);
		$this->setMeta($metas, '_regular_price', $args['grossprice']);
		$this->setMeta($metas, '_sale_price', $args['sellprice']);
		$this->setMeta($metas, '_sku', $args['sku']);
		$this->setMeta($metas, '_stock', $qty);
		$this->setMeta($metas, '_stock_status', $stock_status);
		$this->setMeta($metas, '_thumbnail_id', $thumbnail_id);

		$this->setMeta($metas, '_variation_description', $item['merchitem_descr']);

		return $metas;
	}



	public function tfiweb_addImagesPost(array $images, array $args) : array {
		$parent_post_id = $args['parent_post_id'];

		try {
			
			$imagespost = [];
			foreach ($images as $couchdb_id=>$imagedata) {
				foreach (self::IMAGE_SIZES as $size) {
					$imagedata['size'] = $size;
					$imagefiledata = null;
					$post = $this->createImagePost($imagedata, $args, $imagefiledata);
					$post_id = $this->tfiweb_updatePost($post, [
						'fn_getcurrentpost' => 'createCurrentPostCekParameter'
					]);

					$filelocation = $imagefiledata['filelocation'];

					$imagemetadata = [
						'width' => $size,
						'height' => $size,
						'file' => $filelocation,
						'filesize' => 1000
					];

					if ($size=="600") {
						// tambah meta data size
						// $imagemetadata['sizes'] = [
						// ];


					}


					$metas = [];
					$this->setMeta($metas, '_wp_attached_file', $filelocation);
					$this->setMeta($metas, '_wp_attachment_metadata', serialize($imagemetadata));
					foreach ($metas as $metakey=>$metadata) {
						$meta = $this->createMeta($post_id, $metakey, $metadata);
						$meta_id = $this->tfiweb_updateMeta($meta, $metadata['update']);
					}


					if (!array_key_exists($couchdb_id, $imagespost)) {
						$imagespost[$couchdb_id] = [];
					}

					if (!array_key_exists($size, $imagespost[$couchdb_id])) {
						$imagespost[$couchdb_id][$size] = [
							'post_id' => $post_id,
							'imagefiledata' => $imagefiledata
						];
					}

				}
			}

			return $imagespost;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	public function tfiweb_updateproduct(object $product) : void {
		$sqlCek = "
			select product_id 
			from tfi_wc_product_meta_lookup
			where product_id = :product_id
		";
		$stmtCek = $this->db_tfiweb->prepare($sqlCek);

		try {

			$stmtCek->execute([':product_id' => $product->product_id]);
			$rowCek = $stmtCek->fetch();
			if ($rowCek==null) {
				$product->rating_count = 0;
				$product->average_rating = 0;
				$product->total_sales = 0;

				$cmd = SqlUtility::CreateSQLInsert("tfi_wc_product_meta_lookup", $product);
				$stmtInsert = $this->db_tfiweb->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);	

			} else {

				$keys = new \stdClass;
				$keys->product_id = $product->product_id;

				$cmd = SqlUtility::CreateSQLUpdate("tfi_wc_product_meta_lookup", $product, $keys);
				$stmt = $this->db_tfiweb->prepare($cmd->sql);
				$stmt->execute($cmd->params);

			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function getProductSaleStatus(int $psn, int $producttype, float $grossprice, float $sellprice) : int {
		// $psn = position: 
		// 0 : parent
		// 1 : child

		$onsale = ($sellprice<$grossprice) ? 1 : 0;
		
		if ($producttype==_TAXONOMY_SIMPLE) {
			// status sale ada di parent
			if ($psn==0) {
				return $onsale;
			}

		} else {
			// status sale ada di child	
			if ($psn==1) {
				return $onsale;
			}
		}
		return 0;
	}


	public function createCurrentPostCekParameter(object $post, array $params) : array {
		$sqlwhere = "where post_name = :post_name";
		$sqlparam = [':post_name' => $post->post_name]; 
		return [
			'sqlwhere' => $sqlwhere,
			'sqlparam' => $sqlparam
		];
	}

	public function createPostUpdateFinal(object &$post, array &$params) : void {
		$guidprefix = $params['guidprefix'];
		$post->guid = $guidprefix . $post->ID;
	}

	public function tfiweb_updatePost(object $post, $fn_callbacks=null) : int {

		$fn_getcurrentpost = null;
		$fn_createupdatepost = null;
		$fn_parameter = [];
		if (is_array($fn_callbacks)) {
			if (array_key_exists('fn_getcurrentpost', $fn_callbacks)) {
				$fn_getcurrentpost = $fn_callbacks['fn_getcurrentpost'];
			}

			if (array_key_exists('fn_createupdatepost', $fn_callbacks)) {
				$fn_createupdatepost = $fn_callbacks['fn_createupdatepost'];
			}

			if (array_key_exists('fn_parameter', $fn_callbacks)) {
				$fn_parameter = $fn_callbacks['fn_parameter'];
			}
		}


		try {

			$sqlFieldList = ['post_id'=>'ID', 'itemstock_id'=>'itemstock_id', 'comment_count'=>'comment_count'];
			$sqlwhere = "where itemstock_id = :itemstock_id";
			$sqlparam = [':itemstock_id' => $post->itemstock_id]; 

			$fields = SqlUtility::generateSqlSelectFieldList($sqlFieldList);
			if ($fn_getcurrentpost!=null) {
				$res = $this->$fn_getcurrentpost($post, $fn_parameter);
				$sqlwhere = $res['sqlwhere'];
				$sqlparam = $res['sqlparam'];
			} 

			$sqlCek = "select $fields from tfi_posts $sqlwhere";
			$stmtCek = $this->db_tfiweb->prepare($sqlCek);
			$stmtCek->execute($sqlparam);
			$rowCek = $stmtCek->fetch();
			if ($rowCek==null) {
				// insert
				$post->post_date = $this->getCurrentPostDate(); 
				$post->post_date_gmt = $post->post_date;
		
				$cmd = SqlUtility::CreateSQLInsert("tfi_posts", $post);
				$stmtInsert = $this->db_tfiweb->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);	

				$post_id = $this->db_tfiweb->lastInsertId();

			} else {
				// update
				$post_id = $rowCek['post_id'];
				
				$post->comment_count = $rowCek['comment_count'];
				$post->ID = $post_id;

				$keys = new \stdClass;
				$keys->ID = $post->ID;

				$cmd = SqlUtility::CreateSQLUpdate("tfi_posts", $post, $keys);
				$stmt = $this->db_tfiweb->prepare($cmd->sql);
				$stmt->execute($cmd->params);

			}


			

			// Final Post Update, bila perlu
			if ($fn_createupdatepost!=null) {
				$obj = new \stdClass;
				$obj->ID = $post_id;

				$this->$fn_createupdatepost($obj, $fn_parameter);

				$keys = new \stdClass;
				$keys->ID = $obj->ID;
	
				$cmd = SqlUtility::CreateSQLUpdate("tfi_posts", $obj, $keys);
				$stmt = $this->db_tfiweb->prepare($cmd->sql);
				$stmt->execute($cmd->params);
			}

			return $post_id;
		} catch (\Exception $ex) {
			throw $ex;
		}


	}

	public function tfiweb_updateTermRelationship(object $obj) {
		try {
			//echo "line " . __LINE__ . "\r\n";
			//print_r($obj);

			$sqlFieldList = ['post_id'=>'object_id', 'term_taxonomy_id'=>'term_taxonomy_id', 'term_order'=>'term_order'];
			$sqlwhere = "where `object_id`=:object_id and term_taxonomy_id=:term_taxonomy_id";
			$sqlparam = [':object_id' => $obj->object_id, ':term_taxonomy_id'=>$obj->term_taxonomy_id];
			
			$fields = SqlUtility::generateSqlSelectFieldList($sqlFieldList);
			$sqlCek = "select $fields from tfi_term_relationships $sqlwhere";
			$stmtCek = $this->db_tfiweb->prepare($sqlCek);
			$stmtCek->execute($sqlparam);
			$rowCek = $stmtCek->fetch();
			if ($rowCek==null) {
				// insert term baru
				$cmd = SqlUtility::CreateSQLInsert("tfi_term_relationships", $obj);
				$stmtInsert = $this->db_tfiweb->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);	
			} 
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function tfiweb_getCurrentTermRelationship(int $post_id) : array {
		try {
			$termrellist = [];

			$sqlFieldList = ['post_id'=>'object_id', 'term_taxonomy_id'=>'term_taxonomy_id', 'term_order'=>'term_order'];
			$sqlwhere = "where `object_id`=:object_id";
			$sqlparam = [':object_id' => $post_id];
			
			$fields = SqlUtility::generateSqlSelectFieldList($sqlFieldList);
			$sql = "select $fields from tfi_term_relationships $sqlwhere";
			$stmtCek = $this->db_tfiweb->prepare($sql);
			$stmtCek->execute($sqlparam);
			$rows = $stmtCek->fetchall();
			foreach ($rows as $row) {
				$termrellist[] = $row['term_taxonomy_id'];
			}

			return $termrellist;	
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function tfiweb_cleanTermRelationship(int $post_id, array $prev, array $curr) : void {
		// hapus term relationship yang tidak terpakai (tidak di set di kalista)
		$todelete = [];
		foreach ($prev as $term_taxonomy_id) {
			if (!in_array($term_taxonomy_id, $curr)) {
				$todelete[] = $term_taxonomy_id;
			}
		}

		$sql = "delete from tfi_term_relationships where object_id = :post_id and term_taxonomy_id = :term_taxonomy_id";
		$stmt = $this->db_tfiweb->prepare($sql);			
		foreach ($todelete as $term_taxonomy_id) {
			$stmt->execute([
				':post_id' => $post_id,
				':term_taxonomy_id'=> $term_taxonomy_id
			]);
		}
	}



	public function createPost(array $item, array $args) : object {
		$obj = new stdClass;
		$obj->post_author = $this->TfiWebPostAuthor;
		$obj->post_content = $args['content'];
		$obj->post_title = $item['merchitem_name'];
		$obj->post_excerpt = $args['shortdescr'];
		$obj->post_status = 'publish';
		$obj->comment_status = 'closed';
		$obj->ping_status = 'closed';
		$obj->post_password = '';
		$obj->post_name = $args['post_name'];
		$obj->to_ping = '';
		$obj->pinged = '';
		$obj->post_modified = $this->getCurrentPostDate(); 
		$obj->post_modified_gmt = $obj->post_modified;
		$obj->post_content_filtered	= '';
		$obj->post_parent = $args['parent_post_id'];
		$obj->guid = $this->TfiWebBaseUrl; 
		$obj->menu_order = $args['ordernum'];	
		$obj->post_type = $args['posttype'];
		$obj->post_mime_type = '';
		$obj->comment_count = 0; // diisi nol saat insert	
		$obj->itemstock_id = $args['itemstock_id'];	
		$obj->brand_id = $args['brand_id'];

		return $obj;
	}

	public function createImagePost(array $imagedata, array $args, ?array &$imagefiledata) : object {
		
		$imagefiledata = $this->createImageFileData($imagedata, $args);

		$obj = new \stdClass;
		$obj->post_author = $this->TfiWebPostAuthor;
		$obj->post_content = '';
		$obj->post_title = $imagefiledata['post_title'];
		$obj->post_excerpt = '';
		$obj->post_status = 'inherit';
		$obj->comment_status = 'closed';
		$obj->ping_status = 'closed';
		$obj->post_password = '';
		$obj->post_name = $imagefiledata['post_name'];
		$obj->to_ping = '';
		$obj->pinged = '';
		$obj->post_modified = $this->getCurrentPostDate(); 
		$obj->post_modified_gmt = $obj->post_modified;
		$obj->post_content_filtered	= '';
		$obj->post_parent = $args['parent_post_id'];
		$obj->guid = $imagefiledata['guid'];
		$obj->menu_order = 0;	
		$obj->post_type = 'attachment';
		$obj->post_mime_type = 'image/jpeg';
		$obj->comment_count = 0; // diisi nol saat insert	
		$obj->itemstock_id = '';
		$obj->brand_id = $args['brand_id'];

		return $obj;
	}


	public function createProduct(array $item, array $args, int $post_id) : object {
		$qty = (int)$args['qty'];
		
		$obj = new \stdClass;
		$obj->product_id = $post_id;
		$obj->sku = $args['sku'];
		$obj->virtual = 0;
		$obj->downloadable = 0;
		$obj->min_price = $args['minprice'];
		$obj->max_price = $args['maxprice'];
		$obj->onsale = $args['onsale'];
		$obj->stock_quantity = $qty;
		$obj->stock_status = $qty > 0 ? _INSTOCK : _OUTSTOCK;
		$obj->tax_status = 'taxable';
		$obj->tax_class = $args['taxclass'];
		$obj->brand_id = $args['brand_id'];

		return $obj;
	}

	public function createImageFileData(array $imagedata, array $args) : array {
		
		$brand_id = $args['brand_id'];
		$mercharticle_id = $args['mercharticle_id'];
		$imagefilename = $imagedata['merchpic_name'];
		$imagefileinfo = pathinfo($imagefilename);	
		$filebasename = $imagefileinfo['basename'];
		$filename = $imagefileinfo['filename'];
		$extension = $imagefileinfo['extension'];
		$size = $imagedata['size'];

		$imagefilename = $filename . "-" . $size . "x" . $size . "." . $extension;
		$post_title = strtolower(implode('-', [$brand_id, $mercharticle_id, $filename, $size, $size, $extension]));
		$post_name = "images-" . $post_title;
		
		$imagepath = implode('/', [$mercharticle_id, $imagefilename]);
		$filelocation = implode('/', ['merch', strtolower($brand_id), $imagepath]);
		$guid = implode('/', [$this->TfiWebBaseUrl, 'wp-content/uploads', $filelocation]);

		$ret = [
			'filelocation' =>$filelocation ,
			'filedirname' => $mercharticle_id,
			'filebasename' => $filebasename,
			'imagepath' => $imagepath, 
			'imagefilename' => $imagefilename,
			'size' => $size,
			'post_title' => $post_title,
			'post_name' => $post_name,
			'guid' => $guid
		];

		return $ret;
	}




	public function createShortDescr(array $item, string $posttype, string $producttype, ?string $variantype) : string {
		$descr = '';
		
		if ($posttype=='product') {
			if ($producttype==_TAXONOMY_SIMPLE) {
				$descr = "";
				$descr .= "Article: ". $item['merchitem_art'] ."<br>";
				$descr .= "Color: ". $item['merchitem_colordescr'] ."<br>";

				if (!empty($item['merchitem_pcpgroup'])) {
					$descr .= "Group: ". $item['merchitem_pcpgroup'] ."<br>";
				}

				if (!empty($item['merchitem_pcpcategory'])) {
					$descr .= "Category: ". $item['merchitem_pcpcategory'] ."<br>";						
				}

				$descr .= "Material: " . $item['merchitem_bahan'] . "<br>";
				$descr .= "Care: " . $item['merchitem_pemeliharaan'] . "<br>";

				$descr .= "Weight (Kg): ". $item['merchitem_weight'] ."<br>";
				$descr .= "Size (cm): ". $item['merchitem_width'] . "x" . $item['merchitem_length'] . "x" . $item['merchitem_height']  ."<br>";
				
			} else {
				$descr = '(test test) cek baris' . __LINE__ . ' di ' . __FILE__;
			}
		} else {
			if ($producttype==_TAXONOMY_SIMPLE) {
				$descr = "deskripsi variasi";
			} else {
				$descr = '(test test) cek baris' . __LINE__ . ' di ' . __FILE__;

			}
		}

	
		return $descr ;
	}


	public function getCurrentPostDate() : string {
		$date = date_create(date('Y-m-d'));
		date_add($date, date_interval_create_from_date_string("-2 days"));
		$date = $date->format("Y-m-d");
		return $date;
	}


	function textToSlug(string $text) : string {
		$text = trim($text);
		if (empty($text)) return '';
		$text = preg_replace("/[^a-zA-Z0-9\-\s]+/", "", $text);
		$text = strtolower(trim($text));
		$text = str_replace(' ', '-', $text);
		$text = $text_ori = preg_replace('/\-{2,}/', '-', $text);
		return $text;
	}



	public function tfiweb_updateItemHighlight(array $itemHighlight, array $productpost) : string {
		try {
			$hlitems = [];
			$i=0;
			foreach ($productpost as $post_id) {
				$i++;
				if ($i>12) {
					break;
				}

				$hlitems[] = $post_id;
			} 


			return implode(', ', $hlitems);
		} catch (\Exception $ex) {
			throw $ex;
		}


	}



	public function tfiweb_updateBrandFrontPage($post_id, $data) {
		try {

			$newarrivalsql = "";
			$discsql = "";
			$highlightsql = "";

			if (array_key_exists('newarrival', $data)) {
				$newarrivaldata = $data['newarrival'];
				$newarrivalsql = "
					[vc_row]
						[vc_column]
							[vcex_heading text=\"New Arrival\" padding_all=\"30px\" text_align=\"center\" font_weight=\"bold\"]
							[products columns=\"6\" orderby=\"post__in\" order=\"ASC\" ids=\"$newarrivaldata\"]
						[/vc_column]
					[/vc_row]
				";				
			}

			if (array_key_exists('discount', $data)) {
				$discountdata = $data['discount'];
				$discsql = "
					[vc_row full_width=\"stretch_row\" css=\".vc_custom_1649238537305{background-color: #f9f9f9 !important;}\"]
						[vc_column]
							[vcex_heading text=\"Promo\" padding_all=\"30px\" text_align=\"center\" font_weight=\"bold\"]
							[products columns=\"6\" orderby=\"post__in\" order=\"ASC\" ids=\"$discountdata\"]
						[/vc_column]
					[/vc_row]			
				";
			}

			if (array_key_exists('highlight', $data)) {
				$highlightdata = $data['highlight'];
				$highlightsql = "
					[vc_row]
						[vc_column]
							[vcex_heading text=\"Products\" padding_all=\"30px\" text_align=\"center\" font_weight=\"bold\"]
							[products columns=\"6\" orderby=\"post__in\" order=\"ASC\" ids=\"$highlightdata\"]
							[vcex_button url=\"/product-brand/find-kapoor/\" size=\"medium\" align=\"center\" custom_background=\"#232323\" custom_hover_background=\"#999999\" custom_color=\"#ffffff\" border_radius=\"0px\" margin=\"top:80px\"]
								View More Product List
							[/vcex_button]
						[/vc_column]
					[/vc_row]			
				";


			}				


			$sql = "
			UPDATE `tfi_posts` 
			SET 
			`post_content` = '
				[vc_row visibility=\"hidden-desktop\"]
					[vc_column]
						[vcex_button onclick=\"local_scroll\" url=\"#category\" size=\"medium\" align=\"center\" custom_background=\"accent\" custom_color=\"#ffffff\" border_radius=\"0px\" margin=\"top:30px\" width=\"450px\" custom_hover_background=\"#ffffff\" custom_hover_color=\"accent\"]
							View Categories
						[/vcex_button]
					[/vc_column]
				[/vc_row]
				<!-- BEGIN DYNAMIC DATA -->


				<!-- new arrival -->
				$newarrivalsql

				<!-- discount -->
				$discsql

				<!-- highlight -->
				$highlightsql
				


				<!-- END DYNAMIC DATA -->
				[vc_row local_scroll_id=\"category\"]
					[vc_column]
						[vcex_heading text=\"Category\" padding_all=\"30px\" text_align=\"center\" font_weight=\"bold\"]
					[/vc_column]
				[/vc_row]
				
				[vc_row el_class=\"list-category\"]
					[vc_column width=\"1/5\"]
						[vcex_heading text=\"Accessories\" bottom_margin=\"20px\" responsive_text=\"true\" font_size=\"24px\" min_font_size=\"14px\" link=\"url:%2Fproduct-brand%2Ffind-kapoor%2Faccessories-find-kapoor%2F\" text_transform=\"capitalize\"]
						[vcex_bullets has_icon=\"false\" alignment=\"vertical\" text_transform=\"uppercase\" color=\"#232323\" font_size=\"14px\"]
							<a href=\"/product-brand/find-kapoor/accessories-find-kapoor/straps-find-kapoor/\">Straps</a>
						[/vcex_bullets]
					[/vc_column]
					[vc_column width=\"1/5\"]
						[vcex_heading text=\"Bags\" bottom_margin=\"20px\" responsive_text=\"true\" font_size=\"24px\" min_font_size=\"14px\" link=\"url:%2Fproduct-brand%2Ffind-kapoor%2Fbags-find-kapoor%2F\" text_transform=\"capitalize\"]
						[vcex_bullets has_icon=\"false\" alignment=\"vertical\" text_transform=\"uppercase\" color=\"#232323\" font_size=\"14px\"]
							<a href=\"/product-brand/find-kapoor/bags-find-kapoor/bucket-drawstring-find-kapoor/\">Bucket / Drawstring</a>\r\n
							<a href=\"/product-brand/find-kapoor/bags-find-kapoor/clutches-find-kapoor/\">Clutches</a>\r\n
							<a href=\"/product-brand/find-kapoor/bags-find-kapoor/crossbd-slng-pocte-sdle-find-kapoor/\">Crossbd/Slng/Pocte/Sdle</a>\r\n
							<a href=\"/product-brand/find-kapoor/bags-find-kapoor/shoulder-bags-find-kapoor/\">Shoulder Bags</a>\r\n
							<a href=\"/product-brand/find-kapoor/bags-find-kapoor/top-handles-find-kapoor/\">Top Handles</a>\r\n
							<a href=\"/product-brand/find-kapoor/bags-find-kapoor/tote-shopr-bag-cr-all-find-kapoor/\">Tote/Shopr Bag/Cr All</a>
						[/vcex_bullets]
					[/vc_column]
					[vc_column width=\"1/5\"]
					[/vc_column]
					[vc_column width=\"1/5\"]
					[/vc_column]
					[vc_column width=\"1/5\"]
					[/vc_column]
				[/vc_row]
			'
			WHERE `ID` = :post_id			
			";


			$stmt = $this->db_tfiweb->prepare($sql);
			$stmt->execute([':post_id'=>$post_id]);

		} catch (\Exception $ex) {
			throw $ex;
		}
	}




	public function tfiweb_updateItemNew(array $itemNew) : void {}
	public function tfiweb_updateItemDisc(array $itemDisc) : void {}
	public function tfiweb_updateItemFave(array $itemFave) : void {}





	public function ConnectDb() {
		$TFIWEBDB_CONFIG = DB_CONFIG[$this->TfiWebDb];
		$this->db_tfiweb = new \PDO(
					$TFIWEBDB_CONFIG['DSN'], 
					$TFIWEBDB_CONFIG['user'], 
					$TFIWEBDB_CONFIG['pass'], 
					DB_CONFIG_PARAM['mariadb']
		);

		$cdb = new CouchDbClient((object)DB_CONFIG[$this->KalistaFs]);
		$this->kie = new KalistaImage($cdb);

	}


}



console::class(new class($args) extends clibase {
	function execute() {
		$sync = new SYNC();
		try {
			$sync->setup();
			$sync->ConnectDb();
			$sync->Run();
			exit(0);
		} catch (\Exception $ex) {
			echo "\r\n\r\nERROR.\r\n";
			echo $ex->getMessage();
			echo "\r\n";
			$trace = $ex->getTrace();
			print_r($trace[0]);
			echo "\r\n";
			$sync->ReportError($ex);
			exit(9999);
		} finally {
			echo "\r\n";
		}

	}
});
