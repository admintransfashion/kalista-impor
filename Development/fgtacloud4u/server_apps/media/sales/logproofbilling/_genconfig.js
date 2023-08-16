'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "LogProof Billing",
	autoid: true,
	icon : "icon-logproof-white.svg",
	backcolor : "#025b73",
	committer: true,

	persistent: {
		'trn_medialogproofbilling' : {
			comment: 'Daftar Schedule harian',
			primarykeys: ['medialogproofbilling_id'],
			data: {
				medialogproofbilling_id:  {text:'ID', type: dbtype.varchar(14), null:false},
				medialogproofbilling_date:  {text:'Date', type: dbtype.date, null:false},

				periodemo_id: { 
					text: 'Periode', type: dbtype.varchar(6), null: false, suppresslist: false, 
					options: { required: true, invalidMessage: 'Periode harus diisi' }, 
					comp: comp.Combo({
						table: 'mst_periodemo',
						field_value: 'periodemo_id', field_display: 'periodemo_name',
						api: 'finact/master/periode/list-open'
					})				
				},


				dept_id: {
					text: 'Dept', type: dbtype.varchar(30), null: true, suppresslist: true,
					options: { required: true, invalidMessage: 'Model Transaksi harus diisi' }, 
					comp: comp.Combo({
						table: 'mst_dept',
						field_value: 'dept_id', field_display: 'dept_name',
						api: 'ent/organisation/dept/list-byuser'
					})
				},

				
				medialogproofbilling_version: {text:'Doc Version', type: dbtype.int(4), null:false, default:'0',unset:true, suppresslist: true, options:{disabled:true}},
				medialogproofbilling_iscommit: { text: 'Commit', type: dbtype.boolean, null: false, default: '0', unset:true, options: { disabled: true } },
				medialogproofbilling_commitby: {text:'CommitBy', type: dbtype.varchar(14), suppresslist: true, unset:true, options:{disabled:true}, hidden: true, lookup:'user'},
				medialogproofbilling_commitdate: {text:'CommitDate', type: dbtype.datetime, suppresslist: true, unset:true, comp:comp.Textbox(), options:{disabled:true}, hidden: true},	

				medialogproofbilling_isgenerate: { text: 'Generated', type: dbtype.boolean, null: false, default: '0', unset:true, options: { disabled: true } },
			},
			defaultsearch: ['medialogproofbilling_id', 'medialogproofbilling_date'],
			uniques: {
				'periodemo_id' : ['periodemo_id']
			}
		},

		'trn_medialogproofbillingdet' : {
			comment: 'Daftar Schedule harian',
			primarykeys: ['medialogproofbillingdet_id'],
			data: {
				medialogproofbillingdet_id: { text: 'ID', type: dbtype.varchar(14), null: false, uppercase: true, options: { required: true, invalidMessage: 'ID harus diisi' } },

				mediaorder_id: { text: 'Agency Partner Id', type: dbtype.varchar(14), null: true, options: {disabled: true},
					reference: {table: 'trn_mediaorder', field_value: 'mediaorder_id', field_display:'mediaorder_name',  field_display_name:'mediaorder_name'}, 
				},

				billout_id: { text: 'Billout', type: dbtype.varchar(14), null: true,  options: {disabled: true},
					reference: {table: 'trn_billout', field_value: 'billout_id', field_display:'billout_descr',  field_display_name:'billout_descr'}, 
				},

				medialogproofbilling_gendate:  {text:'Date', type: dbtype.date, null:false},

				medialogproofbilling_id:  {text:'ID', type: dbtype.varchar(14), null:false, hidden: true},
			}
		}

	},

	schema: {
		title: 'LogProof Billing',
		header: 'trn_medialogproofbilling',
		detils: {
			'detil': {title: 'Detil', table: 'trn_medialogproofbillingdet', form: true, headerview: 'medialogproofbilling_date' },
		}
	}
}
