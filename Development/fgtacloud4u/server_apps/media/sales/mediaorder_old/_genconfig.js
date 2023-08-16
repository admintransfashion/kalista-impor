'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Media Order",
	autoid: true,

	persistent: {
		'trn_mediaorder': {
			comment: 'Daftar Media Order',
			primarykeys: ['mediaorder_id'],
			data: {
				mediaorder_id: { text: 'ID', type: dbtype.varchar(30), null: false, uppercase: true, options: { required: true, invalidMessage: 'ID harus diisi' } },
				mediaorder_descr: { text: 'Descr', type: dbtype.varchar(90), null: false, uppercase: true, options: { required: true, invalidMessage: 'Descr harus diisi' } },
			
				mediaorder_date: {text:'Date Start', type: dbtype.date, null:false},

				mediaordertype_id: { 
					text: 'Media Order Type', type: dbtype.varchar(10), uppercase: true, null: false, 
					options: { required: true, invalidMessage: 'Media Order Type harus diisi' }, 
					initialvalue: {id:'RTL', display:'RETAIL'},
					comp: comp.Combo({
						table: 'mst_mediaordertype',
						field_value: 'mediaordertype_id', field_display: 'mediaordertype_name',
						api: 'media/master/mediaordertype/list'
					})				
				},

				agency_partner_id: {
					suppresslist: true,
					options:{required:true,invalidMessage:'Agency harus diisi', prompt:'-- PILIH --'},
					text:'Agency', type: dbtype.varchar(30), null:false, 
					comp: comp.Combo({
						table: 'mst_partner', 
						field_value: 'partner_id', field_display: 'partner_name', 
						api: 'ent/affiliation/partner/list'})
				},	


				advertiser_partner_id: {
					suppresslist: true,
					options:{required:true,invalidMessage:'Adveriser harus diisi', prompt:'-- PILIH --'},
					text:'Advertiser', type: dbtype.varchar(30), null:false, 
					comp: comp.Combo({
						table: 'mst_partner', 
						field_value: 'partner_id', field_display: 'partner_name', 
						api: 'ent/affiliation/partner/list'})
				},				
			
				brand_id: { 
					text: 'Brand', type: dbtype.varchar(10), uppercase: true, null: false, 
					options: { required: true, invalidMessage: 'Brand harus diisi' }, 
					comp: comp.Combo({
						table: 'mst_brand',
						field_value: 'brand_id', field_display: 'brand_name',
						api: 'ent/affiliation/brand/list'
					})				
				},

				mediapackage_id: { 
					text: 'Package', type: dbtype.varchar(10), null: true, suppresslist: true,
					options: { prompt: 'NONE' },
					comp: comp.Combo({
						table: 'mst_mediapackage',
						field_value: 'mediapackage_id', field_display: 'mediapackage_descr',
						api: 'media/master/mediapackage/list'
					})				
				},

				salesordertype_id: { 
					text: 'Order Type', type: dbtype.varchar(10), uppercase: true, null: false, 
					options: { required: true, invalidMessage: 'Order Type harus diisi' , disabled: true }, 
					initialvalue: {id:'MO', display:'MEDIA ORDER'},
					comp: comp.Combo({
						table: 'mst_salesordertype',
						field_value: 'salesordertype_id', field_display: 'salesordertype_name',
						api: 'finact/master/salesordertype/list'
					})				
				},

				doc_id: {
					text:'Doc', type: dbtype.varchar(30), null:false, uppercase: true, suppresslist: true, 
					options: {required:true, invalidMessage:'ID harus diisi', disabled: true },
					initialvalue: {id:'MEDIAORDER', display:'MEDIAORDER'},
					comp: comp.Combo({
						table: 'mst_doc',
						field_value: 'doc_id', field_display: 'doc_name', field_display_name: 'doc_name',
						api: 'ent/organisation/docs/list'
					})				
				},
				
				
			},
			
			defaultsearch: ['mediaorder_id', 'mediaorder_descr']
		},

		'trn_mediaorderitem' : {
			comment: 'Meida Order Item',
			primarykeys: ['mediaorderitem_id'],		
			data: {
				mediaorderitem_id: { text: 'ID', type: dbtype.varchar(14), null: false, uppercase: true, suppresslist: true, },
				mediaorderitem_descr: { text: 'Descr', type: dbtype.varchar(90), null: false, uppercase: true, options: { required: true, invalidMessage: 'Descr harus diisi' } },

				mediaadslot_id: {
					text: 'Slot', type: dbtype.varchar(14), null: true, suppresslist: true,
					options: { prompt: 'NONE' },
					comp: comp.Combo({
						table: 'mst_mediaadslot',
						field_value: 'mediaadslot_id', field_display: 'mediaadslot_descr',
						api: 'media/master/adslot/list'
					})
				},

				itemclass_id: {
					text:'Class', type: dbtype.varchar(14), null:false, uppercase: true, suppresslist: true,
					options: { required: true, invalidMessage: 'Class harus diisi' } ,
					comp: comp.Combo({
						table: 'mst_itemclass', 
						field_value: 'itemclass_id', field_display: 'itemclass_name', field_display_name: 'itemclass_name', 
						api: 'finact/items/itemclass/list',
						criteria: {
							'itemmodel_id' : 'ADS'
						}
					})					
				},


				mediaordertype_id: { 
					text: 'Media Order Type', type: dbtype.varchar(10), uppercase: true, null: false, 
					options: { required: true, invalidMessage: 'Media Order Type harus diisi' }, 
					initialvalue: {id:'RTL', display:'RETAIL'},
					comp: comp.Combo({
						table: 'mst_mediaordertype',
						field_value: 'mediaordertype_id', field_display: 'mediaordertype_name',
						api: 'media/master/mediaordertype/list'
					})				
				},

				mediaorderitem_price: { text: 'Price', type: dbtype.decimal(14, 2), null: false, default: 0, suppresslist: true, options: { required: true } },
				mediaorder_id: { text: 'Order', type: dbtype.varchar(30), null: false, uppercase: true },
			}	
		},

		'trn_mediaorderpayterm' : {
			primarykeys: ['mediaorderpayterm_id'],
			comment: 'Termin pembayaran Media Order',			
			data : {
				mediaorderpayterm_id: { text: 'ID', type: dbtype.varchar(14), null: false, uppercase: true, suppresslist: true, },
				mediaorderpayterm_descr: { text: 'Descr', type: dbtype.varchar(90), null: false, uppercase: true, options: { required: true, invalidMessage: 'Descr harus diisi' } },
				mediaorderpayterm_dt: {text:'Date', type: dbtype.date, null:false, suppresslist: true},
				mediaorderpayterm_value: { text: 'Value', type: dbtype.decimal(14, 2), null: false, default: 0, suppresslist: true, options: { required: true } },
				mediaorder_id: { text: 'Order', type: dbtype.varchar(30), null: false, uppercase: true },
			}
		},



	},

	schema: {
		header: 'trn_mediaorder',
		detils: {
			'items': {title: 'Items', table: 'trn_mediaorderitem', form: true, headerview: 'mediaorder_descr' },
			'payterm' : {title: 'Payment Terms', table: 'trn_mediaorderpayterm', form: true, headerview: 'mediaorder_descr' }
		}
	}


}