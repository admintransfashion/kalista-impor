'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Sales Data",
	autoid: true,

	persistent: {
		'trn_etapsalesdata' : {
			primarykeys: ['etapsalesdata_id'],
			comment: 'Sales Data Etap',
			data: {
				etapsalesdata_id: {text:'ID', type: dbtype.varchar(30), null:false, uppercase: true},
				tx_id: {text:'Descr', type: dbtype.varchar(90), null:false},
				tx_date: {text:'Date Start', type: dbtype.date, null:false},
				
				//supvisit_iscommit: {text:'Commit', type: dbtype.boolean, null:false, suppresslist: false, default:'0'},
				site_id: {
					suppresslist: true,
					options:{required:true,invalidMessage:'Site harus diisi', prompt:'-- PILIH --'},
					text:'Site', type: dbtype.varchar(30), null:false, 
					comp: comp.Combo({
						table: 'mst_site', 
						field_value: 'site_id', field_display: 'site_name', 
						api: 'ent/location/site/list'})
				},

				etapsalesdata_qty: { 
					text: 'Qty', type: dbtype.decimal(18,2), null: false, default:0, suppresslist: true, options: {required: true}},

				etapsalesdata_value: { 
					text: 'Value', type: dbtype.decimal(18,2), null: false, default:0, suppresslist: true, options: {required: true}},
						
				etapsalesdata_tax: { 
					text: 'Tax', type: dbtype.decimal(18,2), null: false, default:0, suppresslist: true, options: {required: true}},
	
			},

			defaultsearch : ['tx_id', 'site_id']
		
		},


	},

	schema: {
		title: 'Site Visit',
		header: 'trn_etapsalesdata',
		detils: {
		}
	}
}



