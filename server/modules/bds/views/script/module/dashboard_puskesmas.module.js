/**
 * @class Bds.module.dashboard_puskesmas
 * Module panel for table bds_dashboard_puskesmas
 *
 * @since 23-10-2012 12:07:20
 * @author wiliamdecosta@gmail.com
 */
Bds.module.dashboard_puskesmas = Ext.extend(Webi.module.Panel, {
    addTitle: 'Tambah Dashboard Puskesmas',
    editTitle: 'Update Dashboard Puskesmas',
    winWidth:550,
    winHeight:480,
    /**
     * initComponent
     * @protected
     */
    initComponent : function() {
        // super
        Bds.module.dashboard_puskesmas.superclass.initComponent.call(this);
    },
    buildPanel : function(){
        this.dashboardUrl = 'index.php?module=bds&class=dashboard_puskesmas&method=show';
        this.jenis_report = new Bds.combo.DashboardRumahSakit({emptyText:'Tampilkan Berdasarkan', width:245, readOnly:true });
        this.bttnPrint = new Ext.Button({text:'Print Xls'});
        this.bttnPrint.on('click',function(){             
            this.dashboardUrl = 'index.php?module=bds&class=dashboard_puskesmas&method=show';
            this.dashboardUrl += '&jenis_report=' + this.jenis_report.getValue();
            this.dashboardUrl += '&t=' + new Date().getTime();
	        this.dashboardUrl += '&print=1';
	        location.href=this.dashboardUrl;
        },this);         
        this.bttnView = new Ext.Button({text:'Tampilkan'});
        this.bttnView.on('click',function(){
            
            /*if(Ext.isEmpty(this.jenis_report.getValue())) {
                Ext.Msg.show({
                   title:'Info',
                   msg: 'Jenis Report Harus Diisi',
                   buttons: Ext.Msg.OK,
                   animEl: 'elId',
                   icon: Ext.MessageBox.INFO
                });      
                return;          
            }*/
            
            this.dashboardUrl = 'index.php?module=bds&class=dashboard_puskesmas&method=show';
            this.dashboardUrl += '&jenis_report=' + this.jenis_report.getValue();
            this.dashboardUrl += '&t=' + new Date().getTime();
            
            this.reloadPanel();
            
        },this);
        
        return {
            itemId:'dashboard-puskesmas',
            autoLoad: {url: this.dashboardUrl},
            border:false,
            autoScroll: true,
            tbar:[
                {xtype: 'tbtext', text: 'Tampilkan Dashboard Berdasarkan :'},
                ' ',
                this.jenis_report,
                ' ', 
                this.bttnView,
                this.bttnPrint
            ]
        };
    },
    buildForm : function(){
        return null;
    },
    
    reloadPanel: function() {
        this.getComponent('dashboard-puskesmas').body.getUpdater().update({url: this.dashboardUrl});    
    }
});

Ext.reg('module_dashboard_puskesmas', Bds.module.dashboard_puskesmas);