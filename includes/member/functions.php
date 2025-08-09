<?php
// File: includes/member/functions.php
include_once '../../config/config.php';

function getAllMembers($conn) {
    return $conn->query("SELECT * FROM member ORDER BY id DESC");
}

function getMemberById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM member WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function createMember($conn, $nama, $no_hp) {
    $stmt = $conn->prepare("INSERT INTO member (nama, no_hp, poin) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $nama, $no_hp);
    return $stmt->execute();
}

function updateMember($conn, $id, $nama, $no_hp) {
    $stmt = $conn->prepare("UPDATE member SET nama = ?, no_hp = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nama, $no_hp, $id);
    return $stmt->execute();
}

function deleteMember($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM member WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
function isPhoneExist($conn, $no_hp, $exclude_id = null) {
    if ($exclude_id) {
        $stmt = $conn->prepare("SELECT id FROM member WHERE no_hp = ? AND id != ?");
        $stmt->bind_param("si", $no_hp, $exclude_id);
    } else {
        $stmt = $conn->prepare("SELECT id FROM member WHERE no_hp = ?");
        $stmt->bind_param("s", $no_hp);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

