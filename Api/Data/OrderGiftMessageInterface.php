<?php

namespace ScaliaGroup\Integration\Api\Data;

interface OrderGiftMessageInterface
{
    /**
     * @return string
     */
    public function getGiftId();

    /**
     * @param string $gift_id
     * @return $this
     */
    public function setGiftId($gift_id);

    /**
     * @return string
     */
    public function getMittente();

    /**
     * @param string $mittente
     * @return $this
     */
    public function setMittente($mittente);

    /**
     * @return string
     */
    public function getDestinatario();

    /**
     * @param string $destinatario
     * @return $this
     */
    public function setDestinatario($destinatario);

    /**
     * @return string
     */
    public function getMessaggio();

    /**
     * @param string $messaggio
     * @return $this
     */
    public function setMessaggio($messaggio);


}
