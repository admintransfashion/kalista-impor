'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Ads Slot",
	autoid: true,

	persistent: {
		'mst_mediaadslot' : {
			comment: 'Daftar Schedule harian',
			primarykeys: ['mediaadslot_id'],
			data: {
				mediaadslot_id:  {text:'ID', type: dbtype.varchar(14), null:false},
				mediaadslot_date:  {text:'Date', type: dbtype.date, null:false},
				mediaadslot_timestart: { text: 'Mulai', type: dbtype.varchar(90), null: false, uppercase: true, options: { required: true, invalidMessage: 'Waktu Mulai harus diisi' } },
				mediaadslot_timeend: { text: 'Selesai', type: dbtype.varchar(90), null: false, uppercase: true, options: { required: true, invalidMessage: 'Waktu Selesai harus diisi' } },
				mediaadslot_descr: { text: 'Descr', type: dbtype.varchar(90), suppresslist: true },

				mediaschprog_timestart: { text: 'Mulai', type: dbtype.varchar(90), null: false, uppercase: true, options: { disabled: true } },
				mediaschprog_timeend: { text: 'Selesai', type: dbtype.varchar(90), null: false, uppercase: true, options: { disabled: true } },
				mediaschprog_notes: { text: 'Note', type: dbtype.varchar(90), suppresslist: true },
				mediaschprog_id: {
					text: 'Sheduled Program', type: dbtype.varchar(14), null: false, suppresslist: false,
					options: { required: true, invalidMessage: 'Program harus diisi', prompt: '-- PILIH --' },
					comp: comp.Combo({
						table: 'mst_mediaschprog',
						field_value: 'mediaschprog_id', field_display: 'mediaprog_name',
						api: 'media/master/mediasch/prog-list'
					})
				},

				itemclass_id: {
					text:'Class', type: dbtype.varchar(14), null:false, uppercase: true, suppresslist: true,
					options: { required: true, invalidMessage: 'Class harus diisi' } ,
					comp: comp.Combo({
						table: 'mst_itemclass', 
						field_value: 'itemclass_id', field_display: 'itemclass_name', field_display_name: 'itemclass_name', 
						api: 'finact/items/itemclass/list'})					
				}

			}
		}
	},

	schema: {
		title: 'Ads Slot',
		header: 'mst_mediaadslot',
		detils: {
		}
	}
}

