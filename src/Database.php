<?php

namespace Src;

use PDO;
use PDOException;

class Database {
    private $host = "localhost";
    private $db_name = "gestao-eventos";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Erro de conexão: " . $exception->getMessage();
        }

        return $this->conn;
    }

    public function getInscricoesByEventoId($eventoId, $limit = null) {
        $conn = $this->getConnection();
        
        if ($conn) {
            try {
                $query = "SELECT 
                            inscricoes.* 
                            FROM 
                                inscricoes 
                            LEFT JOIN certificados_emitidos ON inscricoes.id = certificados_emitidos.id_inscricao 
                            WHERE 
                                id_evento = :eventoId
                            AND 
                                certificados_emitidos.id_inscricao IS NULL";

                if ($limit !== null) {
                    $query .= " LIMIT :limit";
                }

                $stmt = $conn->prepare($query);
                $stmt->bindParam(':eventoId', $eventoId);

                if ($limit !== null) {
                    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                }

                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $exception) {
                echo "Erro na consulta: " . $exception->getMessage();
            }
        }

        return null;
    }

    public function getByEventoId($eventoId) {
        $conn = $this->getConnection();
        
        if ($conn) {
            try {
                $query = "SELECT * FROM eventos WHERE id = :eventoId";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':eventoId', $eventoId);
                $stmt->execute();

                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $exception) {
                echo "Erro na consulta: " . $exception->getMessage();
            }
        }

        return null;
    }

    public function getCertificadoEmitidoByInscricaoId($inscricaoId) {
        $conn = $this->getConnection();
        
        if ($conn) {
            try {
                $query = "SELECT * FROM certificados_emitidos WHERE id_inscricao = :inscricaoId";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':inscricaoId', $inscricaoId);
                $stmt->execute();

                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $exception) {
                echo "Erro na consulta: " . $exception->getMessage();
            }
        }

        return null;
    }

    public function emitirCertificado($inscricaoId, $status) {
        $conn = $this->getConnection();
        
        if ($conn) {
            try {
                $query = "INSERT INTO certificados_emitidos (id_inscricao, status) VALUES (:inscricaoId, :status)";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':inscricaoId', $inscricaoId);
                $stmt->bindParam(':status', $status);
                $stmt->execute();

                return true;
            } catch (PDOException $exception) {
                echo "Erro ao emitir certificado: " . $exception->getMessage();
            }
        }

        return false;
    }
}
