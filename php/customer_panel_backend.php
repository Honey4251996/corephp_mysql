<?php

require_once('db.php');

// date list
$select_date_query = "SELECT DISTINCT date FROM orders";
$date_list = $mysqli->query($select_date_query);

// customer name list
$select_customer_name_query = "SELECT DISTINCT customer_username AS customer_name FROM orders";
$customer_name_list = $mysqli->query($select_customer_name_query);

// filter
if (isset($_POST["date_list"]) && isset($_POST["customer_list"])) {
    $filter_date_list = $_POST["date_list"];
    $filter_customer_list = $_POST["customer_list"];
    $filter_order_query = "SELECT * FROM orders WHERE customer_username='$filter_customer_list' AND date='$filter_date_list'";
    $filter_result = $mysqli->query($filter_order_query);
}


// update status by ajax call
if (isset($_POST['save_changes'])) {
    $field = $_POST['field'];
    $value = $_POST['value'];
    $selected_row = $_POST['selectedRow'];
    $date = date("m/d/Y");

    $update_query = "UPDATE orders SET $field='$value', date='$date' WHERE id='$selected_row'";
    $update_result = $mysqli->query($update_query);

    if ($update_result === TRUE) {
        $response['type'] = "success";
        $response['message'] = "Successfully updated!";
        $response['data'] = $update_result;
    } else {
        $response['type']  = "error";
        $response['message'] = "Update failed!";
        $response['data'] = null;
    }

    echo json_encode($response);
}

// generate invoice pdf
if (isset($_POST['create_invoice'])) {
    $check_filter_customer = $_POST['customer_list'];
    $check_filter_date = $_POST['date_list'];
    $query = "";

    if ($check_filter_date !== "" && $check_filter_customer !== "") {
        $query = "SELECT * FROM orders WHERE customer_username='$check_filter_customer' AND date='$check_filter_date'";
    } else {
        $query = "SELECT * FROM orders";
    }

    $invoice_data = $mysqli->query($query);
    $invoice_body = '';
    foreach ($invoice_data as $row) {
        $invoice_body .= '
            <tr>
                <td class="desc">' . $row["item_name"] . '</td>
                <td class="qty">' . $row["quantity"] . '</td>
                <td class="total">' . $row["cost"] . '</td>
            </tr>
        ';
    }

    $invoice_template = file_get_contents("INVOICE_PDF_HEADER.html");
    $invoice_template .= $invoice_body;
    $invoice_template .= file_get_contents("INVOICE_PDF_FOOTER.html");

    // echo $invoice_template;
    // exit;

    // generate pdf using TCPDF library
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  // create TCPDF object with default constructor args
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->AddPage('P', "A4");
    $pdf->writeHTML($invoice_template);

    $pdf->Output('invoice.pdf', "I");
}


// generate packing slip
if (isset($_POST['creating_packing_slip'])) {
    $check_filter_customer = $_POST['customer_list'];
    $check_filter_date = $_POST['date_list'];
    $query = "";

    if ($check_filter_date !== "" && $check_filter_customer !== "") {
        $query = "SELECT * FROM orders WHERE customer_username='$check_filter_customer' AND date='$check_filter_date'";
    } else {
        $query = "SELECT * FROM orders";
    }

    $packing_slip_data = $mysqli->query($query);
    $packing_slip_body = '';
    foreach ($packing_slip_data as $row) {
        $packing_slip_body .= '
            <tr>
                <td class="desc">' . $row["item_name"] . '</td>
                <td class="qty">' . $row["quantity"] . '</td>
            </tr>
        ';
    }

    $packing_slip_template = file_get_contents("PACKING_PDF_HEADER.html");
    $packing_slip_template .= $packing_slip_body;
    $packing_slip_template .= file_get_contents("PACKING_PDF_FOOTER.html");

    // echo $packing_slip_template;
    // exit;

    // generate pdf using TCPDF library
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  // create TCPDF object with default constructor args
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->AddPage('P', "A4");
    $pdf->writeHTML($packing_slip_template);

    $pdf->Output('packing-slip.pdf', "I");
}
