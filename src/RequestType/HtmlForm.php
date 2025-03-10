<?php

namespace Lunar\BulBank\RequestType;

class HtmlForm extends RequestType
{

    public function send()
    {
        $html = $this->generateForm();

        $html .= '<script>
            document.getElementById("borica3dsRedirectForm").submit()
        </script>';

        return $html;
    }

    public function generateForm()
    {
        $html = '<form
	        action="' . $this->getUrl() . '"
	        style="display: none;"
	        method="POST"
	        id="borica3dsRedirectForm"
        >';

        $inputs = $this->getData();
        foreach ($inputs as $key => $value) {
            $html .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
        }

        $html .= '</form>';

        return $html;
    }
}
