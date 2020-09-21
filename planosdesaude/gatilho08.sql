-- Trigger: BIhabilitacoes

-- DROP TRIGGER "BIhabilitacoes" ON public.habilitacoes;

CREATE TRIGGER "BIhabilitacoes"
    BEFORE INSERT
    ON public.habilitacoes
    FOR EACH ROW
    EXECUTE PROCEDURE public."habilitacoesBI"();

COMMENT ON TRIGGER "BIhabilitacoes" ON public.habilitacoes
    IS 'Um professor somente pode ser habilitado para aulas em um curso quando a instituição de ensino do curso estiver na mesma cidade onde o professor reside. ';
	
===============================================================================================

DECLARE 

	cidcurso INTEGER = NULL;
	cidprof INTEGER = NULL;

BEGIN

	SELECT cpcidade INTO cidcurso FROM (SELECT cpcurso, cpcidade 
		FROM (SELECT cpinstituicao, cpcidade 
		FROM instituicoesensino JOIN (SELECT cpcidade, cplogradouro 
		FROM (SELECT cpcidade, cpbairro 
		FROM cidades JOIN bairros ON cidades.cpcidade = bairros.cecidade) 
		AS C JOIN logradouros ON C.cpbairro = logradouros.cebairro) AS D 
		ON instituicoesensino.celogradouro = D.cplogradouro) AS E JOIN cursos
		ON cursos.ceinstituicao = E.cpinstituicao) AS F WHERE F.cpcurso = new.cecurso;

	SELECT cpcidade INTO cidprof FROM (SELECT cpprofessor, cpcidade 
		FROM professores JOIN (SELECT cpcidade, cplogradouro 
		FROM (SELECT cpcidade, cpbairro 
		FROM cidades JOIN bairros ON cidades.cpcidade = bairros.cecidade) 
		AS A JOIN logradouros ON A.cpbairro = logradouros.cebairro) AS B 
		ON professores.celogradouro = B.cplogradouro) AS G WHERE G.cpprofessor = new.ceprofessor;

	IF cidcurso = NULL THEN 
		cidcurso = new.cidcurso;
	END IF;
	

	IF cidprof = NULL THEN 
		cidprof = new.cidprof;
	END IF;


	IF cidcurso = cidprof THEN 
		RETURN new;
	ELSE
		RAISE EXCEPTION 'Este professor não pode ser habilitado, por não residir na cidade em que o curso será realizado';
		
	END IF;
END;