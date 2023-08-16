'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Schedule",
	autoid: true,

	persistent: {
		'mst_mediasch' : {
			comment: 'Daftar Schedule harian',
			primarykeys: ['mediasch_id'],
			data: {
				mediasch_id:  {text:'ID', type: dbtype.varchar(14), null:false},
				mediasch_date:  {text:'Date', type: dbtype.date, null:false},
				mediasch_iscommit: { text: 'Commit', type: dbtype.boolean, null: false, default: '0', unset:true, options: { disabled: true } },
			},
			defaultsearch: ['mediasch_id'],
			uniques: {
				'mediasch_date' : ['mediasch_date']
			}
		},

		'mst_mediaschprog' : {
			comment: 'Daftar Schedule harian',
			primarykeys: ['mediaschprog_id'],
			data: {
				mediaschprog_id: { text: 'ID', type: dbtype.varchar(14), null: false, uppercase: true, options: { required: true, invalidMessage: 'ID harus diisi' } },
				mediaschprog_timestart: { text: 'Mulai', type: dbtype.varchar(90), null: false, uppercase: true, options: { required: true, invalidMessage: 'Waktu Mulai harus diisi' } },
				mediaschprog_timeend: { text: 'Selesai', type: dbtype.varchar(90), null: false, uppercase: true, options: { required: true, invalidMessage: 'Waktu Selesai harus diisi' } },
				mediaschprog_notes: { text: 'Descr', type: dbtype.varchar(90), suppresslist: true },
				mediaprog_id: {
					text: 'Program', type: dbtype.varchar(14), null: false, suppresslist: false,
					options: { required: true, invalidMessage: 'Program harus diisi', prompt: '-- PILIH --' },
					comp: comp.Combo({
						table: 'mst_mediaprog',
						field_value: 'mediaprog_id', field_display: 'mediaprog_name',
						api: 'media/master/mediaprog/list'
					})
				},	
				mediasch_id:  {text:'Schedule ID', type: dbtype.varchar(14), null:false},
			}
		}
	},

	schema: {
		title: 'Schedule',
		header: 'mst_mediasch',
		detils: {
			'prog': {title: 'Program', table: 'mst_mediaschprog', form: true, headerview: 'mediasch_date' },
		}
	}
}
