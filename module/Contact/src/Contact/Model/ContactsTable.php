<?php

namespace Contact\Model;

use Zend\Db\Sql\Select;

class ContactsTable extends BaseTable
{
    protected $table = 'contacts';

    public function fetchUserContacts($userId, Select $select = null, $filters = array())
    {
        try {
            if (null === $select)
                $select = new Select();

            $select->from($this->table)
                ->where(array("user_id" => $userId));

            if (isset($filters["letter"]) && $filters["letter"] != "" && $filters["letter"] != "all") {
                $select->where->like(
                    'first_name', $filters["letter"] . '%');
            }

            $resultSet = $this->tableGateway->selectWith($select);
            $resultSet->buffer();

            return $resultSet;
        } catch (\Exception $e) {
            $this->logger->err($e->getMessage());
            return array();
        }
    }

    public function getContact($id)
    {
        $id = (int)$id;
        $rowSet = $this->tableGateway->select(array('id' => $id));
        $row = $rowSet->current();
        if (!$row) {
            throw new \Exception("Could not find contact: $id");
        }

        return $row;
    }

    public function saveContact($userId, Contact $contact)
    {
        $data = array(
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone_number' => $contact->phone_number,
            'alternate_number' => $contact->alternate_number,
            "user_id" => $userId
        );

        $id = (int)$contact->id;
        if ($id == 0) {
            $this->insert($data);
        } else {
            if ($this->getContact($id)) {
                $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Contact id does not exist');
            }
        }
    }

    public function deleteContact($userId, $id)
    {
        $contact = $this->getContact($id);

        if ($contact->user_id != $userId) {
            throw new \Exception('You can not delete this contact');
        }

        $this->tableGateway->delete(array('id' => $id));
    }
}