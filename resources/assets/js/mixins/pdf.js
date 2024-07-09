import jsPDF from 'jspdf'

export default {
  methods: {
    exportPDF: function (element) {
      let pdfName = 'export'+(Date.now() / 1000);
      let doc = new jsPDF('l', 'mm', 'a1');
      doc.setFontSize(18);

      if (!element.classList.contains('natcol-table')) {
        let canvasChart = element.querySelector('canvas');
        let chartImage = canvasChart.toDataURL("image/png");
        doc.text("Chart", 10, 10);
        doc.addImage(chartImage, 'JPEG', 15, 40);
      } else {
        let elementHandler = {
          '#ignorePDF': function (element, renderer) {
            return true;
          }
        };

        let source = element.querySelector('.card-body');
        doc.fromHTML(
          source,
          15,
          15,
          {
            'width': 180, 'elementHandlers': elementHandler
          });

        //doc.output("dataurlnewwindow");          
      }

      doc.save(pdfName + '.pdf');
    }
  }
}