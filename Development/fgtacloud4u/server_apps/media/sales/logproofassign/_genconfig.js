'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Log Proof Budget Assign",
	autoid: true,
	icon : "icon-logproof-white.svg",
	backcolor : "#025b73",

	persistent: {

		'trn_medialogproofitem' : {
			comment: 'Daftar Schedule harian',
			primarykeys: ['medialogproofitem_id'],
			data: {
				medialogproofitem_id: { text: 'ID', type: dbtype.varchar(14), null: false, uppercase: true, options: { required: true, invalidMessage: 'ID harus diisi' } },
				mediaadslot_timestart: { text: 'Slot Mulai', type: dbtype.varchar(8), suppresslist: true, null: false, uppercase: true, unset: true, hidden: true, options: { required: true, invalidMessage: 'Waktu Mulai harus diisi' } },
				mediaadslot_timeend: { text: 'Selesai', type: dbtype.varchar(8), suppresslist: true, null: false, uppercase: true, unset: true, hidden: true, options: { required: true, invalidMessage: 'Waktu Selesai harus diisi' } },
				mediaadslot_descr: { text: 'Descr', type: dbtype.varchar(90), suppresslist: false, unset: true, options: {disbled: true} },
				actual_timestart: { text: 'Actual Mulai', type: dbtype.varchar(8), null: false, uppercase: true, options: { required: true, invalidMessage: 'Waktu Actual Mulai harus diisi', disabled: true } },
				actual_timeend: { text: 'Selesai', type: dbtype.varchar(8), null: false, uppercase: true, unset: true, options: { required: true, invalidMessage: 'Waktu Actual Selesai harus diisi', disabled: true } },
				actual_duration: { text: 'Durasi', type: dbtype.decimal(5,2), null: false, uppercase: true, unset: true, options: { required: true, invalidMessage: 'Durasi', disabled: true } },
				spot_id : { text: 'SpotId', type: dbtype.varchar(30), null: false, unset: true, options: { required: true, invalidMessage: 'Spot id harus diisi', disabled: true,  } },

				mediaorderitem_validr: { text: 'Value IDR', type: dbtype.decimal(14, 2), null: false, hidden: true, default: 0, unset: true, options: { required: true } },
				mediaorderitem_ppnidr: { text: 'PPN', type: dbtype.decimal(14, 2), null: false, default: 0, hidden: true, unset: true, options: { required: true } },
				pph_taxtype_id: { text: 'PPh', type: dbtype.varchar(10), null: true, suppresslist: true, hidden: true, unset: true, 
					options: { prompt: 'NONE' }, 
					comp: comp.Combo({
						table: 'mst_taxtype', 
						field_value: 'taxtype_id', field_display: 'taxtype_name', field_display_name: 'pph_taxtype_name', 
						api: 'finact/master/taxtype/list'})				
			
				},

				mediaorder_id: {
					text: 'Media Order', type: dbtype.varchar(14), null: true, unset: true, 
					options: { prompt: 'NONE', disabled: true },
					comp: comp.Combo({
						table: 'trn_mediaorder',
						field_value: 'mediaorder_id', field_display: 'mediaorder_descr',
						api: 'media/sales/mediaorder/list'
					})
				},
				mediaorderitem_id: {
					text: 'Media Order Item', type: dbtype.varchar(14), null: true, suppresslist: true, unset: true, 
					options: { prompt: 'NONE', disabled: true },
					comp: comp.Combo({
						table: 'trn_mediaorderitem',
						field_value: 'mediaorderitem_id', field_display: 'mediaorderitem_descr',
						api: 'media/sales/mediaorder/item-list'
					})
				},	


				projbudget_id: {
					text: 'Budget', type: dbtype.varchar(30), null:true,  suppresslist: true,
					options: { required: true, invalidMessage: 'Budget harus diisi', prompt: '-- PILIH --' },
					comp: comp.Combo({
						table: 'mst_projbudget',
						field_value: 'projbudget_id', field_display: 'projbudget_name',
						api: 'finact/budget/projbudget/list',
						OnSelectedScript: `
				form.setValue(obj.txt_project_id, record.project_id);			
						`
					})
				},

				projbudgettask_id: {
					text: 'Budget Task', type: dbtype.varchar(14), null:true,  suppresslist: true,
					options: { prompt: 'NONE' },
					comp: comp.Combo({
						table: 'mst_projbudgettask',
						field_value: 'projbudgettask_id', field_display: 'projbudgettask_name', field_display_name:'projbudgettask_name',
						api: 'finact/budget/projbudget/task-list',
						staticfilter: `
				criteria.projbudget_id = form.getValue(obj.cbo_projbudget_id);		
						`,
						OnSelectedScript: `
				form.setValue(obj.txt_projecttask_id, record.projecttask_id);			
						`
					})
				},

				billoutpreprocess_id: {
					text: 'Process Bill', type: dbtype.varchar(10), null: true, suppresslist: true, unset: true, 
					options: { prompt: 'NONE', disabled: true },
					comp: comp.Combo({
						table: 'mst_billoutpreprocess',
						field_value: 'billoutpreprocess_id', field_display: 'billoutpreprocess_name',
						api: 'fianct/fin/billoutpreprocess/list'
					})
				},	



				mediaordertype_id: { text: 'MediaOrderType Id', type: dbtype.varchar(10), null: true,  suppresslist: true, unset: true, options: {disabled: true},
					reference: {table: 'mst_mediaordertype', field_value: 'mediaordertype_id', field_display:'mediaordertype_name',  field_display_name:'mediaordertype_name'}, 
				},

				logproof_partnerinfo: { text: 'Partner Info', type: dbtype.varchar(255), suppresslist: true, unset: true, options: { disabled: true } },

				agency_partner_id: { text: 'Agency Partner Id', type: dbtype.varchar(14), null: true, suppresslist: true, unset: true, options: {disabled: true},
					reference: {table: 'mst_partner', field_value: 'partner_id', field_display:'partner_name',  field_display_name:'partner_name'}, 
				},

				advertiser_partner_id: { text: 'Advertiser Partner Id', type: dbtype.varchar(14), null: true,  suppresslist: true,  unset: true, options: {disabled: true},
					reference: {table: 'mst_partner', field_value: 'partner_id', field_display:'partner_name',  field_display_name:'partner_name'}, 
				},
				
				brand_id: { text: 'Brand Id', type: dbtype.varchar(14), null: true,  suppresslist: true, unset: true, options: {disabled: true}, 
					reference: {table: 'mst_brand', field_value: 'brand_id', field_display:'brand_name',  field_display_name:'brand_name'}, 
				},

				project_id: { text: 'Project Id', type: dbtype.varchar(14), null: true, suppresslist: true, options: {disabled: true},
					reference: {table: 'mst_project', field_value: 'project_id', field_display:'project_name',  field_display_name:'project_name'}, 
				},
				
				projecttask_id: { text: 'ProjectTask Id', type: dbtype.varchar(14), null: true, suppresslist: true,  options: {disabled: true},
					reference: {table: 'mst_projecttask', field_value: 'projecttask_id', field_display:'projecttask_name',  field_display_name:'projecttask_name'}, 
				},


				medialogproof_id:  {text:'ID', type: dbtype.varchar(14), null:false, hidden: true},
			}
		},

	},

	schema: {
		title: 'Log Proof',
		header: 'trn_medialogproofitem',
		detils: {
		}
	}
}
