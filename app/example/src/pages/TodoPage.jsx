import { useEffect, useState } from "react";
import { Box, Container, Heading, useToast } from "@chakra-ui/react";
import TodoForm from "../components/TodoForm";
import TodoList from "../components/TodoList";
import axios from "../api/axios";

export default function TodoPage() {
  const [todos, setTodos] = useState([]);
  const toast = useToast();

  const carregarTarefas = async () => {
    try {
      const { data } = await axios.get("/todo");
      console.log("Resposta da API:", data);
      const listaTarefas = Array.isArray(data) ? data : data?.data || [];
      setTodos(listaTarefas);
    } catch (err) {
      console.error("Erro ao carregar tarefas:", err);
      setTodos([]);
      toast({
        title: "Erro ao carregar tarefas",
        status: "error",
        duration: 3000,
        isClosable: true,
      });
    }
  };

  useEffect(() => {
    carregarTarefas();
  }, []);

  const adicionarTarefa = async (descricao) => {
    try {
      await axios.post("/todo", { task: descricao });
      toast({ title: "Tarefa adicionada", status: "success", duration: 2000 });
      await carregarTarefas();
    } catch (err) {
      toast({ title: "Erro ao adicionar tarefa", status: "error", duration: 3000 });
    }
  };

  const alternarStatus = async (tarefa) => {
    try {
      await axios.put(`/todo/${tarefa.id}`, {
        status: tarefa.status === "done" ? "pending" : "done",
        completed_at: tarefa.status === "done" ? null : new Date(),
      });
      await carregarTarefas();
    } catch (err) {
      toast({ title: "Erro ao atualizar status", status: "error", duration: 3000 });
    }
  };

  const removerTarefa = async (tarefa) => {
    try {
      await axios.delete(`/todo/${tarefa.id}`);
      await carregarTarefas();
    } catch (err) {
      toast({ title: "Erro ao deletar tarefa", status: "error", duration: 3000 });
    }
  };

  const favoritarTarefa = async (tarefa) => {
    try {
      await axios.put(`/todo/${tarefa.id}`, {
        favorite: tarefa.favorite ? 0 : 1,
      });
      await carregarTarefas();
    } catch (err) {
      toast({ title: "Erro ao favoritar tarefa", status: "error", duration: 3000 });
    }
  };

  return (
    <Container maxW="md" py={10}>
      <Heading mb={6} textAlign="center">Lista de Tarefas</Heading>
      <TodoForm onAdd={adicionarTarefa} />
      <Box mt={8}>
        <TodoList
          todos={todos}
          onToggle={alternarStatus}
          onDelete={removerTarefa}
          onFavorite={favoritarTarefa}
        />
      </Box>
    </Container>
  );
}
