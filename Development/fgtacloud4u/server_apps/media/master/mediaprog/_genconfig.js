'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Media Programme",
	autoid: true,

	persistent: {
		'mst_mediaprog' : {
			comment: 'Daftar Media Programme',
			primarykeys: ['mediaprog_id'],
			data: {
				mediaprog_id: { text: 'ID', type: dbtype.varchar(14), null: false, uppercase: true, options: { required: true, invalidMessage: 'ID harus diisi' } },
				mediaprog_name: { text: 'Nama', type: dbtype.varchar(90), null: false, uppercase: true, options: { required: true, invalidMessage: 'Nama harus diisi' } },
				mediaprog_descr: { text: 'Descr', type: dbtype.varchar(90), suppresslist: true },
				mediaprog_season: { text: 'Season', type: dbtype.int(4), null: false, default:0 },
				mediaprog_episode: { text: 'Episode', type: dbtype.int(4), null: false, default:0 },
				mediaprog_isdisabled: { text: 'Disabled', type: dbtype.boolean, null: false, default: '0' },

				projbudget_id: {
					text: 'Budget', type: dbtype.varchar(30), null: false, suppresslist: false,
					options: { required: true, invalidMessage: 'Budget harus diisi', prompt: '-- PILIH --' },
					comp: comp.Combo({
						table: 'mst_projbudget',
						field_value: 'projbudget_id', field_display: 'projbudget_name',
						api: 'finact/budget/projbudget/list'
					})
				},

				projbudgettask_id: {
					text: 'Budget Task', type: dbtype.varchar(14), null: true, suppresslist: true,
					options: { prompt: 'NONE' },
					comp: comp.Combo({
						table: 'mst_projbudgettask',
						field_value: 'projbudgettask_id', field_display: 'projecttask_name',
						api: 'finact/budget/projbudget/task-list'
					})
				},				

				dept_id: {
					text: 'Dept', type: dbtype.varchar(30), null: false, suppresslist: false,
					options: { required: true, invalidMessage: 'Departemen harus diisi'},
					comp: comp.Combo({
						table: 'mst_dept',
						field_value: 'dept_id', field_display: 'dept_name', field_display_name: 'dept_name',
						api: 'ent/organisation/dept/list'
					})
				},				
			},
			uniques: {
				'mediaprog_name': ['mediaprog_name'] 
			}  		
		}

	},

	schema: {
		header: 'mst_mediaprog',
		detils: {
			
		}
	}	

}