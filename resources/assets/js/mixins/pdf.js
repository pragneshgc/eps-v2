import jsPDF from 'jspdf'

export default {
    methods: {
        exportPDF: function (element) {
            let pdfName = 'export' + (Date.now() / 1000);
            let doc = new jsPDF('l', 'mm', 'a1');
            //doc.setFontSize(10);

            element.classList.add("hideicon");

            doc.html(element, {
                callback: function (doc) {
                    doc.save(pdfName + ".pdf");
                    element.classList.remove('hideicon');
                },
                autoPaging: 'text',
                x: 0,
                y: 0,
                width: 900,
                //windowWidth: 2000,
            });
        }
    }
}
