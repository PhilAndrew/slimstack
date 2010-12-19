// Copyright © 2010, Esko Luontola <www.orfjackal.net>
// This software is released under the Apache License 2.0.
// The license text is at http://www.apache.org/licenses/LICENSE-2.0

package com.orsa.slimstack.runner;

import java.io.*;

public class ProcessRunner {

    private final ProcessBuilder builder;

    private Process process;
    private Thread shutdownHook;
    private final MulticastPipe outputMulticast = new MulticastPipe();
    private Writer input;

    public ProcessRunner(File liftweb, String... command) {
        builder = new ProcessBuilder(command);
        builder.directory(liftweb);
        builder.redirectErrorStream(true);
    }

    public OutputReader subscribeToOutput() {
        return new OutputReader(outputMulticast.subscribe());
    }

    public void start() throws IOException {
        process = builder.start();
        shutdownHook = new Thread(new DestroyProcessRunner(process));

        InputStreamReader output = new InputStreamReader(new BufferedInputStream(process.getInputStream()));
        Thread t = new Thread(new ReaderToWriterCopier(output, outputMulticast));
        t.setDaemon(true);
        t.start();

        input = new OutputStreamWriter(new BufferedOutputStream(process.getOutputStream()));
    }

    public void destroyOnShutdown() {
        Runtime.getRuntime().addShutdownHook(shutdownHook);
    }

    public void destroy() {
        process.destroy();
        try {
            process.waitFor();
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
        Runtime.getRuntime().removeShutdownHook(shutdownHook);
    }

    public boolean isAlive() {
        try {
            process.exitValue();
            return false;
        } catch (IllegalThreadStateException e) {
            return true;
        }
    }

    public void writeInput(String s) throws IOException {
        input.write(s);
        input.flush();
    }

}
