import { Button, Input, Stack } from "@chakra-ui/react";
import { useState } from "react";

export default function TodoForm({ onAdd }) {
  const [task, setTask] = useState("");

  const handleSubmit = (e) => {
    e.preventDefault();
    if (task.trim() !== "") {
      onAdd(task);
      setTask("");
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <Stack direction="row">
        <Input
          placeholder="Nova tarefa"
          value={task}
          onChange={(e) => setTask(e.target.value)}
        />
        <Button colorScheme="teal" type="submit">Adicionar</Button>
      </Stack>
    </form>
  );
}
