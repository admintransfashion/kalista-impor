'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Site Visit",
	autoid: true,

	persistent: {
		'trn_supvisit' : {
			primarykeys: ['supvisit_id'],
			comment: 'Master Site Visit',
			data: {
				supvisit_id: {text:'ID', type: dbtype.varchar(14), null:false, uppercase: true},
				supvisit_descr: {text:'Descr', type: dbtype.varchar(90), null:false},
				supvisit_datestart: {text:'Date Start', type: dbtype.date, null:false},
				supvisit_dateend: {text:'Date End', type: dbtype.date, null:false},
				supvisit_iscommit: {text:'Commit', type: dbtype.boolean, null:false, suppresslist: false, default:'0'},
				land_id: {
					suppresslist: true,
					options:{required:true,invalidMessage:'Land harus diisi', prompt:'-- PILIH --'},
					text:'Land', type: dbtype.varchar(30), null:false, 
					comp: comp.Combo({
						table: 'mst_land', 
						field_value: 'land_id', field_display: 'land_name', 
						api: 'ent/location/land/list'})
				}
			},

			defaultsearch : ['supvisit_id', 'supvisit_descr']
		
		},


		'trn_supvisitsite' : {
			primarykeys: ['supvisitsite_id'],
			comment: 'Site yang dikunjungi user',
			data: {
				supvisitsite_id: {text:'ID', type: dbtype.varchar(14), null:false, suppresslist: true},
				site_id: {
					text:'Site', type: dbtype.varchar(30), null:false, uppercase: true,
					options:{required:true,invalidMessage:'Site harus diisi', prompt:'-- PILIH --'},
					comp: comp.Combo({
						table: 'mst_site', 
						field_value: 'site_id', field_display: 'site_name', 
						api: 'ent/location/site/list'})				
				},
				supvisit_id: {text:'Visit', type: dbtype.varchar(14), null:false, uppercase: true},
			},
			uniques: {
				'user_id' : ['supvisit_id', 'site_id']
			}			
		},


		'trn_supvisituser' : {
			primarykeys: ['supvisituser_id'],
			comment: 'User yang mengunjungi site',
			data: {
				supvisituser_id: {text:'ID', type: dbtype.varchar(14), null:false, suppresslist: true},
				user_id: {
					text:'User', type: dbtype.varchar(14), null:false,
					options:{required:true,invalidMessage:'User harus diisi', prompt:'-- PILIH --'},
					comp: comp.Combo({
						table: 'fgt_user', 
						field_value: 'user_id', field_display: 'user_name', 
						api: 'fgta/framework/fguser/list'})					
				
				},
				supvisit_id: {text:'Visit', type: dbtype.varchar(14), null:false, uppercase: true},
			},
			uniques: {
				'user_id' : ['supvisit_id', 'user_id']
			}			
		}
	},

	schema: {
		title: 'Site Visit',
		header: 'trn_supvisit',
		detils: {
			'site' : {title: 'Site', table:'trn_supvisitsite', form: true, headerview:'supvisit_descr'},  
			'user' : {title: 'User', table:'trn_supvisituser', form: true, headerview:'supvisit_descr'}
		}
	}
}



