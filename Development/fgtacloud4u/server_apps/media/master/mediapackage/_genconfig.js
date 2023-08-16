'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Media Package",
	autoid: true,

	persistent: {
		'mst_mediapackage' : {
			comment: 'Daftar Package Media',
			primarykeys: ['mediapackage_id'],
			data: {
				mediapackage_id:  {text:'ID', type: dbtype.varchar(14), null:false},
				mediapackage_descr: { text: 'Descr', type: dbtype.varchar(90), suppresslist: true }
			},
			defaultsearch: ['mediapackage_id', 'mediapackage_descr'],
		},

		'mst_mediapackageslot' : {
			comment: 'Daftar Slot untuk Package Media',
			primarykeys: ['mediapackageslot_id'],
			data: {
				mediapackageslot_id:  {text:'ID', type: dbtype.varchar(14), null:false},
				mediaadslot_id: {
					text: 'Slot', type: dbtype.varchar(14), null: false, suppresslist: false,
					options: { required: true, invalidMessage: 'Slot harus diisi', prompt: '-- PILIH --' },
					comp: comp.Combo({
						table: 'mst_mediaadslot',
						field_value: 'mediaadslot_id', field_display: 'mediaadslot_descr',
						api: 'media/master/adslot/list'
					})
				},
				mediapackage_id:  {text:'ID', type: dbtype.varchar(14), null:false},
			}
		},

	},

	schema: {
		title: 'Media Package',
		header: 'mst_mediapackage',
		detils: {
			'slot': {title: 'Slots', table: 'mst_mediapackageslot', form: true, headerview: 'mediapackage_descr' },
		}
	}
}

