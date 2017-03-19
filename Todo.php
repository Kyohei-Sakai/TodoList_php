<?php

namespace TodoObject;

class Todo {
  private $_db;

  function __construct() {
    $this->_createToken();

    try {
      $this->_db = new \PDO(DSN, DB_USERNAME, DB_PASSWARD);
      $this->_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
      echo $e->getMessage();
      exit;
    }
  }

  private function _createToken() {
    if (!isset($_SESSION['token'])) {
      // tokenの発行の仕方の一例
      $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(16));
    }
  }

  public function getAll() {
    $stmt = $this->_db->query("select * from todos order by id desc");
    return $stmt->fetchAll(\PDO::FETCH_OBJ);
  }

  public function post() {
    $this->validateToken();

    if (!isset($_POST['mode'])) {
      throw new Exception("mode not set!");
    }

    switch ($_POST['mode']) {
      case 'update':
        return $this->_update();
      case 'create':
        return $this->_create();
      case 'delete':
        return $this->_delete();
      case 'delete done':
        return $this->_deleteDone();
    }
  }

  private function validateToken() {
    if (
      !isset($_SESSION['token']) ||
      !isset($_POST['token']) ||
      $_SESSION['token'] !== $_POST['token']
    ) {
      throw new Exception("invalid token!");
    }
  }

  private function _update() {
    if (!isset($_POST['id'])) {
      throw new Exception("[update] id not set!");
    }

    // アクセスが集中した場合に、idがずれて正しく処理が行われないことを防ぐ
    $this->_db->beginTransaction();

    // state = 0 -> 1 or 1 -> 0
    // % を文字列の中で使う場合 -> %%
    $sql = sprintf("update todos set state = (state + 1) %% 2 where id = %d", $_POST['id']);
    $stmt = $this->_db->prepare($sql);
    $stmt->execute();

    $sql = sprintf("select state from todos where id = %d", $_POST['id']);
    $stmt = $this->_db->query($sql);
    $state = $stmt->fetchColumn();

    $this->_db->commit();

    return [
      'state' => $state
    ];
  }

  private function _create() {
    if (!isset($_POST['title']) || $_POST['title'] === '') {
      throw new Exception("[create] title not set!");
    }

    $sql = "insert into todos (title, created) values (:title, :created)";
    $stmt = $this->_db->prepare($sql);
    $stmt->execute([
      ':title' => $_POST['title'],
      'created' => date("Y-m-d H:i:s")
    ]);

    return [
      'id' => $this->_db->lastInsertId()
    ];
  }

  private function _delete() {
    if (!isset($_POST['id'])) {
      throw new Exception("[delete] id not set!");
    }

    $sql = sprintf("delete from todos where id = %d", $_POST['id']);
    $stmt = $this->_db->prepare($sql);
    $stmt->execute();

    return [];
  }

  private function _deleteDone() {

    $sql = "select id from todos where state = 1";
    $stmt = $this->_db->query($sql);
    $ids = $stmt->fetchAll(\PDO::FETCH_OBJ);
    // foreach ($stmt as $) {
    //   $user->show();
    // }

    $sql = "delete from todos where state = 1";
    $stmt = $this->_db->prepare($sql);
    $stmt->execute();

    return ['ids' => $ids];
    // [{id : *}, {id : *}, ..]
  }


}
