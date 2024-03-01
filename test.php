<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pcb extends PC_Controller
{

    private $ci;
    public function __construct()
    {
        parent::__construct();
        // $this->load->model('pcb_model');
        $this->ci = CI();
        $this->ci->load->library('aws');
        // $this->load->helper('find_index');
        // $this->load->helper('custom_array_map');
    }

    public function approve()
    {
        $params = $this->accepted_params();
        //
        // echo json_encode($params);die();
        $params = $this->validate($params, 'approve');
        $params = $this->validate($params, 'approve');
        $params = $this->validate($params, 'approve');
        $params = $this->validate($params, 'approve');
        $params = $this->validate($params, 'approve');

        $result = $this->coreapi->hk_application_approval($params);

        return $this->res(200, null, $result);
    }

    public function test()
    {
        $params = $this->accepted_params();
        //
        // echo json_encode($params);die();
        $params = $this->validate($params, 'approve');

        $result = $this->coreapi->hk_application_approval($params);

        return $this->res(200, null, $result);
    }

    public function get_pcb_user()
    {
        $params = $this->accepted_params();
        //
        // echo json_encode($params);die();
        // $params = $this->validate($params, 'approve');

        $result = $this->coreapi->hk_get_pcb_users($params);

        return $this->res(200, null, $result);
    }

    public function resend()
    {
        $params = $this->accepted_params();
        $this->validate($params, 'resend');
        $params["action"] = "RESEND";
        $result = $this->coreapi->resend_application_email($params);
        $this->res(200, null);
    }

    public function add_documents()
    {
        $params = $this->accepted_params();
        $documents = array(
            'documents' => json_decode($params['document'], true),
        );

        $this->ci->aws->upload_upload_attachment_help_center_pcb_document(
            $_FILES,
            $params['reference_no'] . "/" . 'upload_pcb_documents',
            FILE_TYPE_BUSINESS_DOCUMENTS,
            'upload_pcb_documents',
            null
        );
        // echo json_encode($_FILES);die();
        $ticket_image = $this->ci->aws->get_pcb_images($params['reference_no']) ?? null;
        // echo json_encode($ticket_image);die();
        $size_ticket_image = sizeof($ticket_image);

        for ($j = 0; $j < $size_ticket_image; $j++) {

            $ticket_image[$j]->image_url = 'https://paychat-deployments-mobilehub-466268124.s3.ap-southeast-1.amazonaws.com/' . $ticket_image[$j]->scalar;

            if ($j == 0) {
                $ext = '';
            } else {
                $ext = $j;
            }

            if (isset($_FILES['document' . ($j + 1)])) {
                $ticket_image[$j]->filename = $_FILES['document' . ($j + 1)]['name'];
            } else {

                $ticket_image[$j]->filename = 'Default Filename';
            }

            if (isset($documents['documents'][$j])) {
                $ticket_image[$j]->document = $documents['documents'][$j];
            } else {

                $ticket_image[$j]->document = 'Default Document Type';
            }

            unset($ticket_image[$j]->scalar);
        }

        $result = array(
            'reference_no' => $params['reference_no'],
            'pcb_web_apply_id' => $params['pcb_web_apply_id'],
            'action' => $params['action'],
            'docs' => $ticket_image,
        );

        $result = $this->coreapi->process_pcb_applications($result);

        return $this->res(200, null, $ticket_image);
    }

    public function get_documents()
    {
        $params = $this->accepted_params();

        $ticket_image = $this->ci->aws->get_pcb_images($params['reference_no']) ?? null;

        $size_ticket_image = sizeof($ticket_image);

        for ($j = 0; $j < $size_ticket_image; $j++) {

            $ticket_image[$j]->image_url = 'https://paychat-deployments-mobilehub-466268124.s3.ap-southeast-1.amazonaws.com/' . $ticket_image[$j]->scalar;

            if ($j == 0) {
                $ext = '';
            } else {
                $ext = $j;
            }

            $ticket_image[$j]->filename = 'documents' . $ext . '.pdf';

            $ticket_image[$j]->document_type = 'Type A';

            unset($ticket_image[$j]->scalar);
        }

        return $this->res(200, null, $ticket_image);
    }

    public function applications()
    {
        $params = $this->accepted_params();
        $result = $this->coreapi->get_pcb_applications($params);
        $this->res(200, null, $result, (sizeOf($result) < 1) ? 1 : sizeOf($result));
    }

    public function process()
    {
        $params = $this->accepted_params();
        $result = $this->coreapi->process_pcb_applications($params);
        $this->res(200, null, $result, (sizeOf($result) < 1) ? 1 : sizeOf($result));
    }

    public function update()
    {
        $params = $this->accepted_params();

        $result = $this->coreapi->update_pcb_applications($params);
        $this->res(200, null, $result, (sizeOf($result) < 1) ? 1 : sizeOf($result));
    }

    public function pcb_count_apply()
    {
        // $params="1";
        // // $result = $this->coreapi->update_pcb_applications($params);
        // $result = $this->coreapi->get_pcb_count($params);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://abc.cebuanalhuillier.com/CorporatePayoutApi/GetEWalletBalance/1.0');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'KeyName: PAYCHAT_publickey',
            'x-api-key: 916a6dd1-83ff-47d7-a889-78e5d21872d6',
            'Content-Type: application/json',
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{
			"Request": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJQYXlsb2FkIjoie1wiQ3JlZGVudGlhbHNcIjp7XCJVc2VyTmFtZVwiOlwiUEFZQ0hBVF9DUFNVQVRcIixcIlBhc3N3b3JkXCI6XCJQNHlDaGF0QDEyM1wifX0ifQ.ZvuIeZcQANWWX6sGZ9DKbfgMVhPW3y0dDGqgKSqjiMrMLujJNzmt-c1MHK8HlJxfI01hlZvy68iLD4q1i1L5or70CMFikwRhKdvmnIubsymKmx84VSCpD9rtq6p5K2wzN4Qfu7kUvvNex_EU5_481Am-95MwZqCzZDYgX7e2b4x0EBJfx1vc5AxRr2vQEGaLLSot-VD9WZxND4-anCjEqBiZSDBdQmqD-3H2yem4bZNW7IrlNWP7tpA_fH6HmJhrgezVhUIPnBC6V93aodf0j6LanM6nOsfPMeUDyAfPLPQSHEfDq99tx9puFGm6bkWElz02RA2PjEljrxBuSPLN1w"
		  }');

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            $error_message = curl_error($ch);
            echo "cURL Error: " . $error_message;
        } else {
            echo "HTTP Code: " . json_encode($httpcode);

            $data = json_decode($response, true);
            $result = $data['Result'];
        }

        echo json_encode($result);
        die();

        return $this->res(200, null, $result);
    }

    public function get_representatives()
    {
        $params = $this->accepted_params();

        // $this->validate($params, 'get');
        $result = $this->coreapi->get_representatives($params);
        $this->res(200, null, $result);
    }

    public function pcb_requirements()
    {
        $params = $this->accepted_params();
        $this->validate($params, 'get');
        $result = $this->coreapi->get_pcb_requirements($params);
        $this->res(200, null, $result);
    }

    private function _file_validations($files)
    {
        $allowed_file_types = ["image/png", "image/jpeg", "application/pdf", 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $size_limit = 5000000;

        foreach ($files as $key => $file) {
            if (!in_array($file["type"], $allowed_file_types)) {
                return 0;
            }

            if ($file["size"] > $size_limit) {
                return -1;
            }

        }

        return true;
    }

    private function _single_upload_files($file, $reference_id)
    {
        $file_extesion = $this->_get_file_extension($file["type"]);
        $link = $this->ci->aws->putObject($file["tmp_name"], $reference_id, $file["type"], FILE_TYPE_BUSINESS_DOCUMENTS, $file_extesion);
        if (!$link) {
            $this->res(404, null);
        }

        return $link;
    }

    private function _check_documents($data)
    {
        $data["is_active"] = 1;
        $documents = $this->coreapi->get_hm_pcb_apply_documents($data);

        /** sync database data to user input */
        $x = custom_array_map(function ($map_val, $map_ind, $data) {
            $json = json_decode($data["documents"], true);

            $index = find_index(function ($find_val, $find_ind, $map_val) {

                if (!isset($find_val["pcb_apply_documents_id"])) {
                    return false;
                }

                return $find_val["pcb_apply_documents_id"] == $map_val->pcb_apply_documents_id;
            }, $json, $map_val);

            if ($index >= 0) {
                $map_val->is_verified = $json[$index]["is_verified"];
            }

            return $map_val;
        }, $documents, $data);

        if ($documents) {
            foreach ($documents as $docs) {
                if ($docs->is_verified == '0') {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    private function _upload_files($files, $reference_id)
    {

        $file_inserted = [];

        foreach ($files as $key => $file) {
            $file_extesion = $this->_get_file_extension($file["type"]);
            $link = $this->ci->aws->putObject($file["tmp_name"], $reference_id, $file["type"], FILE_TYPE_BUSINESS_DOCUMENTS, $file_extesion);
            if (!$link) {
                $this->res(404, null);
            }

            $file_inserted[$key] = $link;
        }

        return $file_inserted;
    }

    private function _get_file_extension($file_type)
    {
        $file_extesions = [
            "image/jpeg" => ".jpg",
            "image/png" => ".png",
            "application/pdf" => ".pdf",
            "application/msword" => ".doc",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => ".docx",
        ];

        if (!isset($file_extesions[$file_type])) {
            return false;
        }

        return $file_extesions[$file_type];
    }
}
