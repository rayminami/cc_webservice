/**
 * @class Bds.grid.param_industri
 * Grid for table bds_param_industri
 *
 * @author wiliamdecosta@gmail.com
 * @since 23-10-2012 12:07:20
 */
Bds.grid.param_industri = Ext.extend(Webi.grid.GridPanel, {
    viewConfig:{forceFit:false},
    enableAdd:false,
    enableEdit:false,
    enableDelete:false,
    initComponent : function() {
        this.store = new Bds.store.p_parameter();
        this.store.baseParams.kode_type = 'JENIS INDUSTRI';
        
        this.columns = [
            {header: Bds.properties.param_id, hidden: true, sortable: true, dataIndex: 'param_id'},
			{header: Bds.properties.ptype_id, hidden: true, sortable: true, dataIndex: 'ptype_id', width: 65, renderer: Webi.format.intRenderer},
			{header: Bds.properties.param_code, hidden: true, sortable: true, dataIndex: 'param_code', width:138},
			{header: 'Jenis Industri', hidden: false, sortable: true, dataIndex: 'param_name', width:226}
        ];

        // super
        Bds.grid.param_industri.superclass.initComponent.call(this);
    },
    getDefaultData: function(){
        var defaultData = {
			'param_id': '',
			'param_code': '',
			'param_name': '',
			'param_listing_no': '',
			'param_description': '',
			'param_creation_date': '',
			'param_creation_by': '',
			'param_updated_date': '',
			'param_updated_by': ''
        };
        return defaultData;
    }
});
Ext.reg('grid_param_industri', Bds.grid.param_industri);