'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Log Proof",
	autoid: true,
	icon : "icon-logproof-white.svg",
	backcolor : "#025b73",
	committer: true,

	persistent: {
		'trn_medialogproof' : {
			comment: 'Daftar Schedule harian',
			primarykeys: ['medialogproof_id'],
			data: {
				medialogproof_id:  {text:'ID', type: dbtype.varchar(14), null:false},
				medialogproof_date:  {text:'Date', type: dbtype.date, null:false},

				medialogproof_version: {text:'Doc Version', type: dbtype.int(4), null:false, default:'0',unset:true, suppresslist: true, options:{disabled:true}},
				medialogproof_iscommit: { text: 'Commit', type: dbtype.boolean, null: false, default: '0', unset:true, options: { disabled: true } },
				medialogproof_commitby: {text:'CommitBy', type: dbtype.varchar(14), suppresslist: true, unset:true, options:{disabled:true}, hidden: true, lookup:'user'},
				medialogproof_commitdate: {text:'CommitDate', type: dbtype.datetime, suppresslist: true, unset:true, comp:comp.Textbox(), options:{disabled:true}, hidden: true},	


				medialogproof_isgenerate: { text: 'Generated', type: dbtype.boolean, null: false, default: '0', unset:true, options: { disabled: true } },
			},
			defaultsearch: ['medialogproof_id', 'medialogproof_date'],
			uniques: {
				'medialogproof_date' : ['medialogproof_date']
			}
		},

		'trn_medialogproofitem' : {
			comment: 'Daftar Schedule harian',
			primarykeys: ['medialogproofitem_id'],
			data: {
				medialogproofitem_id: { text: 'ID', type: dbtype.varchar(14), null: false, uppercase: true, options: { required: true, invalidMessage: 'ID harus diisi' } },
				mediaadslot_timestart: { text: 'Slot Mulai', type: dbtype.varchar(8), suppresslist: true, null: false, uppercase: true, options: { required: true, invalidMessage: 'Waktu Mulai harus diisi' } },
				mediaadslot_timeend: { text: 'Selesai', type: dbtype.varchar(8), suppresslist: true, null: false, uppercase: true, options: { required: true, invalidMessage: 'Waktu Selesai harus diisi' } },
				mediaadslot_descr: { text: 'Descr', type: dbtype.varchar(90), suppresslist: false },
				actual_timestart: { text: 'Actual Mulai', type: dbtype.varchar(8), null: false, uppercase: true, options: { required: true, invalidMessage: 'Waktu Actual Mulai harus diisi' } },
				actual_timeend: { text: 'Selesai', type: dbtype.varchar(8), null: false, uppercase: true, options: { required: true, invalidMessage: 'Waktu Actual Selesai harus diisi' } },
				actual_duration: { text: 'Durasi', type: dbtype.decimal(5,2), null: false, uppercase: true, options: { required: true, invalidMessage: 'Durasi' } },
				spot_id : { text: 'SpotId', type: dbtype.varchar(30), null: false, options: { required: true, invalidMessage: 'Spot id harus diisi' } },

				mediaorderitem_validr: { text: 'Value IDR', type: dbtype.decimal(14, 2), null: false, default: 0, options: { required: true } },
				mediaorderitem_ppnidr: { text: 'PPN', type: dbtype.decimal(14, 2), null: false, default: 0, options: { required: true } },
				pph_taxtype_id: { text: 'PPh', type: dbtype.varchar(10), null: true, suppresslist: true,
					options: { prompt: 'NONE' }, 
					comp: comp.Combo({
						table: 'mst_taxtype', 
						field_value: 'taxtype_id', field_display: 'taxtype_name', field_display_name: 'pph_taxtype_name', 
						api: 'finact/master/taxtype/list'})				
			
				},

				mediaorder_id: {
					text: 'Media Order', type: dbtype.varchar(14), null: true,
					options: { prompt: 'NONE' },
					comp: comp.Combo({
						table: 'trn_mediaorder',
						field_value: 'mediaorder_id', field_display: 'mediaorder_descr',
						api: 'media/sales/mediaorder/list'
					})
				},
				mediaorderitem_id: {
					text: 'Media Order Item', type: dbtype.varchar(14), null: true, suppresslist: true,
					options: { prompt: 'NONE' },
					comp: comp.Combo({
						table: 'trn_mediaorderitem',
						field_value: 'mediaorderitem_id', field_display: 'mediaorderitem_descr',
						api: 'media/sales/mediaorder/item-list'
					})
				},	
				mediaordertype_id: { text: 'MediaOrderType Id', type: dbtype.varchar(10), null: true,  suppresslist: true, options: {disabled: true},
					reference: {table: 'mst_mediaordertype', field_value: 'mediaordertype_id', field_display:'mediaordertype_name',  field_display_name:'mediaordertype_name'}, 
				},

				logproof_partnerinfo: { text: 'Partner Info', type: dbtype.varchar(255), suppresslist: true, options: { disabled: true } },

				agency_partner_id: { text: 'Agency Partner Id', type: dbtype.varchar(14), null: true, suppresslist: true, options: {disabled: true},
					reference: {table: 'mst_partner', field_value: 'partner_id', field_display:'partner_name',  field_display_name:'partner_name'}, 
				},

				advertiser_partner_id: { text: 'Advertiser Partner Id', type: dbtype.varchar(14), null: true,  suppresslist: true,  options: {disabled: true},
					reference: {table: 'mst_partner', field_value: 'partner_id', field_display:'partner_name',  field_display_name:'partner_name'}, 
				},
				
				brand_id: { text: 'Brand Id', type: dbtype.varchar(14), null: true,  suppresslist: true, options: {disabled: true}, 
					reference: {table: 'mst_brand', field_value: 'brand_id', field_display:'brand_name',  field_display_name:'brand_name'}, 
				},


				project_id: { text: 'Project Id', type: dbtype.varchar(14), null: true, suppresslist: true, options: {disabled: true},
					reference: {table: 'mst_project', field_value: 'project_id', field_display:'project_name',  field_display_name:'project_name'}, 
				},
				
				projecttask_id: { text: 'Project Id', type: dbtype.varchar(14), null: true, suppresslist: true,  options: {disabled: true},
					reference: {table: 'mst_projecttask', field_value: 'projecttask_id', field_display:'projecttask_name',  field_display_name:'projecttask_name'}, 
				},


				medialogproof_id:  {text:'ID', type: dbtype.varchar(14), null:false, hidden: true},
			}
		},

		'trn_medialogproofupload' : {
			comment: 'Daftar Schedule harian',
			primarykeys: ['medialogproofupload_id'],
			data: {
				medialogproofupload_id: { text: 'ID', type: dbtype.varchar(14), null: false,  options: { required: true, invalidMessage: 'ID harus diisi' } },
				mediaadslot_timestart: { text: 'SlotTimeStart', type: dbtype.varchar(8), suppresslist: true, null: false,options: { required: true, invalidMessage: 'Waktu Mulai harus diisi' } },
				mediaadslot_timeend: { text: 'SlotTimeEnd', type: dbtype.varchar(8), suppresslist: true, null: false,  options: { required: true, invalidMessage: 'Waktu Selesai harus diisi' } },
				mediaadslot_duration: { text: 'SlotDuration', type: dbtype.decimal(6,2), suppresslist: true, null: false,   options: { required: true, invalidMessage: 'Durasi harus diisi' } },
				mediaadslot_descr: { text: 'SlotDescr', type: dbtype.varchar(255) },
				mediaadslot_code: { text: 'SlotCode', type: dbtype.varchar(255) , suppresslist: true, },

				actual_timestart: { text: 'ActualTimeStart', type: dbtype.varchar(8), null: false,  options: { required: true, invalidMessage: 'Waktu Actual Mulai harus diisi' } },
				actual_timeend: { text: 'ActualTimeEnd', type: dbtype.varchar(8), null: false, options: { required: true, invalidMessage: 'Waktu Actual Selesai harus diisi' } },
				actual_duration: { text: 'ActualDuration', type: dbtype.decimal(6,2), null: false, options: { required: true, invalidMessage: 'Durasi harus diisi' } },

				spot_id : { text: 'SpotId', type: dbtype.varchar(30),  null: false, options: { required: true, invalidMessage: 'Spot id harus diisi' } },

				mediaorder_ref: { text: 'MeidaOrderRef', type: dbtype.varchar(30), suppresslist: true, },
				mediaorder_reftype: { text: 'TypeMediaOrder', type: dbtype.varchar(30), suppresslist: true, },
				mediaorder_descr: { text: 'MediaOrderDescr', type: dbtype.varchar(255), suppresslist: true, },
				mediaorder_id: { text: 'MediaOrderId', type: dbtype.varchar(30), suppresslist: true, hidden: true, unset:true, options: {disabled: true} },
				mediaordertype_id: { text: 'MediaOrderId', type: dbtype.varchar(10), suppresslist: true, hidden: true, unset:true, options: {disabled: true},
					reference: {table: 'mst_mediaordertype', field_value: 'mediaordertype_id', field_display:'mediaordertype_name',  field_display_name:'mediaordertype_name'}, 
				},

				agency_code: { text: 'AgencyCode', type: dbtype.varchar(30), suppresslist: true, },
				agency_name: { text: 'AgencyName', type: dbtype.varchar(90) },
				agency_partner_id: { text: 'Agency Partner Id', type: dbtype.varchar(14), suppresslist: true, hidden: true, unset:true, options: {disabled: true},
					reference: {table: 'mst_partner', field_value: 'partner_id', field_display:'partner_name',  field_display_name:'partner_name'}, 
				},

				advertiser_code: { text: 'AdvertiserCode', type: dbtype.varchar(30), suppresslist: true, },
				advertiser_name: { text: 'AdvertiserName', type: dbtype.varchar(90) },
				advertiser_partner_id: { text: 'Advertiser Partner Id', type: dbtype.varchar(14), suppresslist: true, hidden: true, unset:true, options: {disabled: true},
					reference: {table: 'mst_partner', field_value: 'partner_id', field_display:'partner_name',  field_display_name:'partner_name'}, 
				},

				brand_code: { text: 'BrandCode', type: dbtype.varchar(30), suppresslist: true, },
				brand_name: { text: 'BrandName', type: dbtype.varchar(90) },
				brand_id: { text: 'Brand Id', type: dbtype.varchar(14), suppresslist: true, hidden: true, unset:true, options: {disabled: true},
					reference: {table: 'mst_brand', field_value: 'brand_id', field_display:'brand_name',  field_display_name:'brand_name'}, 
				},

				programme_code: { text: 'ProgrammeCode', type: dbtype.varchar(30), suppresslist: true, },
				programme_name: { text: 'ProgrammeName', type: dbtype.varchar(90) },
				project_id: { text: 'Project Id', type: dbtype.varchar(14), suppresslist: true, hidden: true, unset:true, options: {disabled: true},
					reference: {table: 'mst_project', field_value: 'project_id', field_display:'project_name',  field_display_name:'project_name'}, 
				},

				episode_code: { text: 'EpisodeCode', type: dbtype.varchar(30) , suppresslist: true,},
				episode_name: { text: 'EpisodeName', type: dbtype.varchar(90) },
				projecttask_id: { text: 'Project Id', type: dbtype.varchar(14), suppresslist: true, hidden: true, unset:true, options: {disabled: true},
					reference: {table: 'mst_projecttask', field_value: 'projecttask_id', field_display:'projecttask_name',  field_display_name:'projecttask_name'}, 
				},
				
				medialogproof_validr: { text: 'Total IDR', type: dbtype.decimal(14, 2), null: false, default: 0, options: { required: true } },
				medialogproof_ppnidr: { text: 'PPN', type: dbtype.decimal(14, 2), null: false, default: 0, options: { required: true } },
				pph_taxtype_id: { text: 'PPh', type: dbtype.varchar(10), null: true, suppresslist: true,
					options: { prompt: 'NONE' }, 
					comp: comp.Combo({
						table: 'mst_taxtype', 
						field_value: 'taxtype_id', field_display: 'taxtype_name', field_display_name: 'pph_taxtype_name', 
						api: 'finact/master/taxtype/list'})				
			
				},

				medialogproof_id:  {text:'ID', type: dbtype.varchar(14), null:false, hidden: true},
			}
		}
	},

	schema: {
		title: 'Log Proof',
		header: 'trn_medialogproof',
		detils: {
			'item': {title: 'Item', table: 'trn_medialogproofitem', form: true, headerview: 'medialogproof_date' },
			'upload': {title: 'Upload', table: 'trn_medialogproofupload', form: true, headerview: 'medialogproof_date' },
		}
	}
}
